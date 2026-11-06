<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Reporte;
use App\Models\Seguimiento;
use App\Models\AdjuntoReporte;
use App\Models\CorreoEnvio;
use App\Services\NotificadorCorreo;
use App\Services\TextoEnriquecido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ticketsController extends Controller
{
    public function show(){

        $reportes = Reporte::with('adjuntos')
        ->where('user_id', Auth::user()->id)
        ->orderBy('created_at', 'desc')->simplePaginate(4);

        $limites = self::limitesAdjuntos();

        $reenviables = CorreoEnvio::idsConUltimoFallo(
            Reporte::class,
            $reportes->pluck('id')->all()
        );

        return view('user.perfil_tickets', compact('reportes', 'limites', 'reenviables'));

    }

    /** Formatos de evidencia aceptados, en texto para los mensajes de error. */
    private const FORMATOS_ADJUNTOS = 'jpeg, jpg, png, gif, webp, pdf, doc, docx, xls, xlsx, ppt, pptx, csv';

    private const MAX_ADJUNTOS = 5;
    // En kilobytes, que es la unidad que usa la regla `max:` de Laravel.
    // El tope real no lo marca esta app sino la plataforma: Vercel rechaza con
    // 413 cualquier cuerpo de peticion mayor a 4.5 MB antes de que PHP lo vea, y
    // ese limite no se puede levantar con php.ini. Se publican 4 MB para no
    // prometer un máximo inalcanzable; lo que se pase de ahi, lo resuelve el
    // compresor del navegador reducing la imagen antes de enviarla.
    private const MAX_PESO_UNO = 4096;     // 4 MB
    private const MAX_PESO_TOTAL = 4096;   // 4 MB
    // Vercel devuelve 413 FUNCTION_PAYLOAD_TOO_LARGE a partir de 4.5 MB de cuerpo.
    private const TOPE_PLATAFORMA_KB = 4096;

    /** Mime -> extension. La extension sale del contenido detectado, no del nombre que manda el cliente. */
    private const EXTENSION_POR_MIME = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-word.document.macroEnabled.12' => 'docm',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/vnd.ms-excel.sheet.macroEnabled.12' => 'xlsm',
        'text/csv' => 'csv',
        'application/vnd.ms-powerpoint' => 'ppt',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
    ];

    public function reporte(){

        $reglas = request('dispositivo') == 'Otro'
            ? ['otro' => 'required']
            : ['dispositivo' => 'required'];

        $reglas['prioridad'] = 'required';
        $reglas['descripcion'] = 'required|string|max:10000';
        $reglas['adjuntos']    = 'nullable|array|max:' . self::MAX_ADJUNTOS;
        $reglas['adjuntos.*']  = 'file|mimes:' . str_replace(', ', ',', self::FORMATOS_ADJUNTOS)
            . '|max:' . self::limitesAdjuntos()['maxUnoKb'];

        // Se revisa antes de validar: si PHP ya rechazo el archivo, las reglas de
        // abajo nunca llegan a correr y el unico mensaje que sale es el generico.
        $this->revisarErroresDeSubida();

        request()->validate($reglas, [
            'adjuntos.*.mimes' => 'El :attribute no es un formato admitido. Se aceptan imágenes '
                . '(JPG, PNG, GIF, WEBP) y documentos PDF, Word, Excel, PowerPoint o CSV.',
            'adjuntos.*.max'   => 'El :attribute supera el máximo de '
                . round(self::limitesAdjuntos()['maxUnoKb'] / 1024, 1) . ' MB.',
            'adjuntos.max'      => 'Se pueden adjuntar hasta ' . self::MAX_ADJUNTOS . ' archivos.',
        ], [
            'adjuntos.*' => 'archivo adjunto',
            'adjuntos'   => 'los adjuntos',
        ]);

        // Quill manda HTML; el 'required' de arriba lo cumple un <p><br></p> vacío,
        // así que el contenido se comprueba sobre el texto plano.
        if (!TextoEnriquecido::tieneContenido(request('descripcion'))) {
            throw ValidationException::withMessages([
                'descripcion' => 'Escribe la descripción de la falla.',
            ]);
        }

        $archivos = $this->validarAdjuntos(request()->file('adjuntos', []));

        $descripcion = TextoEnriquecido::limpiar(request('descripcion'));

        $reporte = Reporte::create([
            'dispositivo'   => request('dispositivo'),
            'descripcion'   => $descripcion,
            'prioridad'     => request('prioridad'),
            'fecha_reporte' => substr(Carbon::now(), 0, 10),
            'otro'          => request('otro'),
            'user_id'       => Auth::user()->id,
            // La columna legacy se sigue llenando con la primera imagen para no
            // romper nada anterior; la lista real de archivos es reporte_adjuntos.
            'img'           => '',
        ]);

        $this->guardarAdjuntos($reporte, $archivos);

        $reporte = Reporte::with(['user', 'adjuntos'])->findOrFail($reporte->id);

        // La columna legacy no se llena sola al guardar los adjuntos, así que se
        // escribe aparte para no dejar reportes antiguos con la imagen vacía.
        $reporte->img = $this->primeraImagen($reporte);
        Reporte::whereKey($reporte->id)->update(['img' => $reporte->img]);

        // El reporte y los adjuntos ya estan guardados. El aviso a soporte va
        // aparte y nunca puede romper la peticion: si el SMTP falla, el usuario
        // igual ve su exito y una nota discreta, en vez de un 500 que lo
        // invitaba a mandar el mismo reporte otra vez.
        $envio = NotificadorCorreo::soporte($reporte);
        if (! $envio['ok'] && $envio['intentos'] > 0) {
            session()->flash('correo_fallido', $envio['error']);
        }

        return back()->with('reportado', 'El reporte fue enviado!');

    }

    /**
     * Reenvia el aviso de un reporte ya guardado. Solo puede hacerlo su dueno
     * (el reporte se busca por user_id, no por id), y el resultado nunca es un
     * 500: si el SMTP vuelve a fallar, se guarda la auditoria y se avisa.
     */
    public function reenviar_reporte($id)
    {
        $reporte = Reporte::where('user_id', Auth::user()->id)
            ->findOrFail($id);

        $envio = NotificadorCorreo::soporte($reporte);

        if (! $envio['ok']) {
            session()->flash('correo_fallido', $envio['error'] ?: 'No se pudo contactar el servidor de correo.');
            return back()->with('reenvio_fallido', 'El reporte sigue guardado, pero el aviso por correo falló.');
        }

        return back()->with('reenviado', 'El aviso fue reenviado correctamente.');
    }

    /**
     * Topes de la evidencia, en una sola fuente de verdad. La vista los recibe
     * para no hardcodear un máximo que despues no se cumpla.
     *
     * El tope que manda es el mas pequeno de los tres: lo que declara la app, lo
     * que acepta PHP en este servidor y lo que acepta la plataforma. Si la app
     * promete mas de lo que PHP admite, el usuario vuelve a toparse con el
     * generico "Subir adjuntos.0 ha fallado", que es justo lo que se quiere evitar.
     *
     * @return array{maxArchivos:int, maxUnoKb:int, maxTotalKb:int, limitePhpKb:int}
     */
    public static function limitesAdjuntos(): array
    {
        $limitePhp = self::limiteRealDeSubida();

        $uno = self::MAX_PESO_UNO;
        $total = self::MAX_PESO_TOTAL;

        if ($limitePhp > 0) {
            $uno = min($uno, $limitePhp);
            $total = min($total, $limitePhp);
        }

        $uno = min($uno, self::TOPE_PLATAFORMA_KB);
        $total = min($total, self::TOPE_PLATAFORMA_KB);

        return [
            'maxArchivos' => self::MAX_ADJUNTOS,
            'maxUnoKb'    => $uno,
            'maxTotalKb'  => $total,
            'limitePhpKb' => $limitePhp,
        ];
    }

    /**
     * El limite de PHP tal cual esta configurado, no el que la app declara.
     * Es el numero que hay que mostrar cuando PHP rechaza el archivo, porque es
     * el que realmente se alcanzo.
     */
    private static function limiteRealDeSubida(): int
    {
        $crudo = strtolower(trim((string) ini_get('upload_max_filesize')));
        $numero = (int) $crudo;
        $sufijo = substr($crudo, -1);

        if ($numero <= 0) {
            return 0; // 0 en php.ini significa sin limite
        }

        switch ($sufijo) {
            case 'g':
                return $numero * 1024 * 1024;
            case 'm':
                return $numero * 1024;
            case 'k':
                return $numero;
            default:
                return $numero;
        }
    }

    /**
     * Traduce los errores de subida de PHP a un mensaje util.
     *
     * Sin esto Laravel responde con el generico "Subir adjuntos.0 ha fallado"
     * (resources/lang/es/validation.php:125) y ademas lo hace en
     * Validator.php:591, antes de evaluar las reglas de mimes y max, asi que
     * nunca se llega a saber si el problema era el peso o el formato.
     */
    private function revisarErroresDeSubida(): void
    {
        $limitePhp = self::limiteRealDeSubida();
        $limite = $limitePhp > 0 ? round($limitePhp / 1024, 1) . ' MB' : 'sin limite';

        $mensajes = [
            UPLOAD_ERR_INI_SIZE =>
                'supera el máximo de ' . $limite . ' por archivo que acepta el servidor.',
            UPLOAD_ERR_FORM_SIZE =>
                'supera el tamaño máximo permitido por el formulario.',
            UPLOAD_ERR_PARTIAL =>
                'se subió incompleto. Inténtalo de nuevo.',
            UPLOAD_ERR_NO_FILE =>
                'no se recibio ningun archivo.',
            UPLOAD_ERR_CANT_WRITE =>
                'no se pudo escribir en el servidor. Intentalo de nuevo.',
            UPLOAD_ERR_EXTENSION =>
                'una extension del servidor detuvo la subida.',
        ];

        $problemas = [];

        foreach ((array) request()->file('adjuntos', []) as $archivo) {
            $error = $archivo->getError();

            if ($error === UPLOAD_ERR_OK) {
                continue;
            }

            // No se reporta UPLOAD_ERR_NO_FILE cuando el campo vino vacio, que es
            // el caso normal de un reporte sin evidencia.
            if ($error === UPLOAD_ERR_NO_FILE && empty(request('adjuntos'))) {
                continue;
            }

            $problemas[] = 'El archivo "' . $archivo->getClientOriginalName() . '" '
                . ($mensajes[$error] ?? 'no se pudo subir.');
        }

        if ($problemas) {
            throw ValidationException::withMessages(['adjuntos' => $problemas]);
        }
    }

    /**
     * Reglas que una regla de validación no puede cubrir: peso total y resolución
     * mínima, que aplica solo a imágenes y no a PDF/Word (la regla `dimensions`
     * rechazaría un documento).
     *
     * @param  array<int, \Illuminate\Http\UploadedFile>  $archivos
     * @return array<int, \Illuminate\Http\UploadedFile>
     */
    private function validarAdjuntos(array $archivos)
    {
        $total = 0;
        $problemas = [];

        foreach ($archivos as $archivo) {
            $total += (int) $archivo->getSize();
            $mime = (string) $archivo->getMimeType();

            if (!str_starts_with($mime, 'image/')) {
                continue;
            }

            $medidas = @getimagesize($archivo->getRealPath());
            if ($medidas === false) {
                $problemas[] = 'No se pudo leer la imagen "' . $archivo->getClientOriginalName() . '".';
                continue;
            }
            if ($medidas[0] < 300 || $medidas[1] < 300) {
                $problemas[] = 'La imagen "' . $archivo->getClientOriginalName() . '" es de '
                    . $medidas[0] . '×' . $medidas[1] . ' px; el mínimo es 300×300.';
            }
        }

        // $total va en bytes (getSize), el tope esta en KB, asi que se comparan
        // en la misma unidad.
        $maximoTotal = self::limitesAdjuntos()['maxTotalKb'] * 1024;

        if ($total > $maximoTotal) {
            $problemas[] = 'La evidencia suma ' . round($total / 1048576, 1)
                . ' MB y el máximo es ' . round($maximoTotal / 1048576, 1) . ' MB.';
        }

        if ($problemas) {
            throw ValidationException::withMessages(['adjuntos' => $problemas]);
        }

        return $archivos;
    }

    /**
     * @param  array<int, \Illuminate\Http\UploadedFile>  $archivos
     */
    private function guardarAdjuntos(Reporte $reporte, array $archivos): void
    {
        $disco = Storage::disk('local');
        $carpeta = 'reportes/' . $reporte->id;

        foreach ($archivos as $archivo) {
            $mime = (string) $archivo->getMimeType();
            $extension = self::EXTENSION_POR_MIME[$mime] ?? 'bin';

            // El nombre en disco se arma aqui y no con el que envia el cliente,
            // para que nada pueda escapar de la carpeta ni pisar otro archivo.
            $nombreEnDisco = trim(Str::slug(pathinfo($archivo->getClientOriginalName(), PATHINFO_FILENAME)), '-')
                . '-' . Str::random(8) . '.' . $extension;

            $ruta = $archivo->storeAs($carpeta, $nombreEnDisco, 'local');

            AdjuntoReporte::create([
                'reporte_id'      => $reporte->id,
                'ruta'            => $ruta,
                'nombre_original' => mb_substr($archivo->getClientOriginalName(), 0, 255),
                'mime'            => $mime,
                'tamano'          => (int) $archivo->getSize(),
                'es_imagen'       => str_starts_with($mime, 'image/'),
            ]);
        }
    }

    private function primeraImagen(Reporte $reporte): string
    {
        $imagen = $reporte->adjuntos->firstWhere('es_imagen', true);

        return $imagen ? $imagen->ruta : '';
    }



    public function reporte_completo($id){

        request()->validate([
            'calificacion' => 'required|integer|between:1,5',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $reportes = Reporte::findOrFail($id);
        $reportes->status = 'completado';
        $reportes->calificacion = (int) request('calificacion');
        $reportes->observaciones = request('observaciones') ?: null;
        $reportes->fecha_solucion = substr(Carbon::now(), 0, 10);
        $reportes->save();
     
        return back()->with('completado', 'El reporte fue cerrado con éxito!');

    }


    public function detalle_reporte($id){

        
        $reporte = Reporte::with(['adjuntos', 'user'])->findOrFail($id);
        $comentarios = Seguimiento::where('reporte_id', $id)->get();
        return view('user.detalle_reporte', compact('reporte', 'comentarios'));

    }


    public function detalle_reporte_admin($id){


        $reporte = Reporte::with(['user', 'adjuntos'])->findOrFail($id);
        $comentarios = Seguimiento::where('reporte_id', $id)->get();
        return view('admin.detalle_reporte', array_merge(compact('reporte', 'comentarios'), [
            'rutaVolver' => 'admin.soporte',
            'rutaComentario' => 'comentario.reporte.admin',
            'nombreAgente' => Auth::guard('admin')->user()->nombre,
            'navView' => 'assets.nav',
        ]));

    }


    /**
     * Detalle para el agente de soporte: misma vista que el admin, lo unico que
     * cambia son las rutas del menu y a donde se vuelve.
     */
    public function soporte_detalle_reporte($id)
    {
        $reporte = Reporte::with(['user', 'adjuntos'])->findOrFail($id);
        $comentarios = Seguimiento::where('reporte_id', $id)->orderBy('id')->get();

        return view('admin.detalle_reporte', array_merge(compact('reporte', 'comentarios'), [
            'rutaVolver' => 'soporte.index',
            'rutaComentario' => 'soporte.comentario',
            'nombreAgente' => Auth::guard('soporte')->user()->nombre,
            'navView' => 'assets.nav_soporte',
        ]));
    }





    public function comentario_usuario($id){
        

        $usuario = Auth::user()->name;
        $fecha = $this->fechaSeguimiento();


        request()->validate([
            'comentario' => 'required'
        ]);

        //guardando los datos con la asignacion masiva
        Seguimiento::create([
            'usuario' => $usuario,
            "fecha" => $fecha,
            'reporte_id' => $id,
            'comentario' => request('comentario')
        ]);


        return back()->with('comentado', 'Listo! ');


    }


    public function comentario_admin($id){


        $usuario = Auth::guard('admin')->user()->nombre;
        $fecha = $this->fechaSeguimiento();


        request()->validate([
            'comentario' => 'required'
        ]);

        //guardando los datos con la asignacion masiva
        Seguimiento::create([
            'usuario' => $usuario,
            "fecha" => $fecha,
            'reporte_id' => $id,
            'comentario' => request('comentario')
        ]);


        return back()->with('comentado', 'Listo! ');





    }


    /**
     * Respuesta del agente de soporte. El ticket lo cierra y califica el
     * usuario; aqui unicamente se responde.
     */
    public function soporte_comentario($id)
    {
        request()->validate([
            'comentario' => 'required|string|max:2000',
        ]);

        // Si el ticket no existe el seguimiento no debe quedar huerfano.
        Reporte::findOrFail($id);

        Seguimiento::create([
            'usuario' => Auth::guard('soporte')->user()->nombre,
            'fecha' => $this->fechaSeguimiento(),
            'reporte_id' => $id,
            'comentario' => request('comentario'),
        ]);

        return back()->with('comentado', 'Respuesta enviada al usuario.');
    }


    /**
     * La columna fecha es un string: antes se guardaba 'Y-m-d H' y se perdia el
     * minuto, asi que dos respuestas del mismo minuto quedaban indistinguibles.
     */
    private function fechaSeguimiento()
    {
        return Carbon::now()->format('Y-m-d H:i');
    }


    /**
     * Ruta legacy: servía la imagen desde la columna `img`. Ahora la evidencia
     * se entrega por adjuntos.show, así que esto solo se mantiene para enlaces
     * viejos y aplica la misma autorización que aquella ruta. Sin esto, cualquier
     * usuario autenticado podía pedir la imagen de un reporte ajeno.
     */
    public function mostrar_imagen($id){
        $reporte = Reporte::with('adjuntos')->findOrFail($id);

        if (!Auth::guard('admin')->check()
            && !Auth::guard('soporte')->check()
            && (int) Auth::id() !== (int) $reporte->user_id) {
            abort(404);
        }

        // Si la evidencia ya vive en reporte_adjuntos, esa es la ruta buena.
        $primera = $reporte->adjuntos->firstWhere('es_imagen', true);
        if ($primera && $primera->existeEnDisco()) {
            return redirect()->route('adjuntos.show', $primera->id);
        }

        if (!$reporte->img || !Storage::disk('local')->exists($reporte->img)) {
            abort(404);
        }

        return response()->file(Storage::disk('local')->path($reporte->img));
    }



}
