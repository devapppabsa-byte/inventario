<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Pedido;
use App\Models\Reporte;
use App\Models\PostUser;
use App\Models\Telefono;
use App\Models\Impresora;
use App\Models\Resguardo;
use App\Models\Computadora;
use App\Models\Proveedor;
use App\Models\Evaluacion;
use App\Models\EvaluacionUsuario;
use App\Models\Publicacion;
use App\Models\EmailSetting;
use App\Services\XlsxReader;
use App\Services\XlsxWriter;
use App\Services\EntregaPedido;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;



    public function show_formulario(){

        $usuarios = User::with('evaluaciones')->get();
        $evaluaciones = Evaluacion::where('activo', true)
            ->with(['seccionesActivas' => function($q){ $q->where('activo', true); }])
            ->orderBy('titulo')->get();

        return view('admin.agregar_usuarios', compact('usuarios', 'evaluaciones'));
    }



    public function registrar_usuarios(){

    
        request()->validate([
            'nombre' => 'required',
            'email' => 'required|email|unique:users,correo',
            'puesto' => 'required',
            'correo' => 'required|email|',
            'password' => 'required',
            'planta' => 'required',
            'extension' => 'required',
        ]);
        // No se permite dejar una encuesta marcada sin subencuetas: se bloquea todo el guardado
        $validacionSecciones = $this->seccionesValidasPorEvaluacion(
            request('evaluaciones', []),
            request('secciones', [])
        );

        if (count($validacionSecciones['errores']) > 0) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'secciones' => $validacionSecciones['errores'],
            ]);
        }




        $usuario = new  User();

        $usuario->name = request('nombre');
        $usuario->email = request('email');
        $usuario->puesto = request('puesto');
        $usuario->planta = request('planta');
        $usuario->jefe = 0;
        $usuario->extension = request('extension');
        $usuario->correo = request('correo');
        $usuario->celular = request('celular');
        $usuario->password = bcrypt(request('password'));
        $usuario->save();

        $errores = $this->sincronizarEvaluacionesUsuario($usuario, request('evaluaciones', []), $validacionSecciones['secciones']);

        if (count($errores) > 0) {
            return back()->with('agregado', 'El usuario fue agregado, pero no se le asignaron las siguientes evaluaciones (faltó seleccionar secciones): ' . implode(', ', $errores));
        }

        return back()->with('agregado', 'El usuario fue agregado');

    }

    /**
     * Reglas de reconocimiento de columnas del Excel, en orden.
     * Gana la primera que coincide, así que las más específicas van primero:
     * `correo_para_entrar` debe resolverse a `email` antes de que la regla
     * genérica de `correo` se lo trague.
     */
    private const REGLAS_COLUMNAS = [
        ['contiene' => ['password'], 'exacto' => [], 'campo' => 'password'],
        ['contiene' => ['entrar', 'login', 'acceso'], 'exacto' => ['email'], 'campo' => 'email'],
        ['contiene' => ['mostrar', 'visible', 'correo'], 'exacto' => [], 'campo' => 'correo'],
        ['contiene' => ['nombre'], 'exacto' => ['name'], 'campo' => 'name'],
        ['contiene' => ['area'], 'exacto' => ['puesto'], 'campo' => 'puesto'],
        ['contiene' => ['planta'], 'exacto' => [], 'campo' => 'planta'],
        ['contiene' => ['extension', 'ext'], 'exacto' => [], 'campo' => 'extension'],
        ['contiene' => ['celular', 'telefono', 'tel', 'movil'], 'exacto' => [], 'campo' => 'celular'],
    ];

    private const COLUMNAS_ESPERADAS = 'nombre, area, planta, telefono, extension, correo_para_mostrar, correo_para_entrar, password';

    public function importar_usuarios_excel(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx',
        ]);

        $ruta = $request->file('archivo')->getRealPath();
        try {
            $filas = XlsxReader::leer($ruta);
        } catch (\Throwable $e) {
            return back()->withErrors(['archivo' => 'No se pudo leer el archivo: ' . $e->getMessage()])
                         ->withInput();
        }

        if (empty($filas)) {
            return back()->withErrors(['archivo' => 'El archivo está vacío']);
        }

        // 1) Encabezados = la fila con más celdas
        $ancho       = 0;
        $encabezados = null;
        $numEnc      = null;
        foreach ($filas as $num => $fila) {
            $valores = array_values($fila);
            if (count($valores) > $ancho) {
                $ancho       = count($valores);
                $encabezados = $valores;
                $numEnc      = $num;
            }
        }
        if (!$encabezados) {
            return back()->withErrors(['archivo' => 'El archivo está vacío']);
        }

        // 2) Mapa columna => campo según nombre de encabezado
        $mapa = [];
        foreach ($encabezados as $i => $h) {
            $campo = $this->campoDeEncabezado(strtolower(trim((string) $h)));
            if ($campo) {
                $mapa[$i] = $campo;
            }
        }

        $campos = array_values($mapa);
        $faltan = $this->columnasFaltantes($campos);
        if ($faltan) {
            return back()->withErrors(['archivo' => 'Columnas requeridas no encontradas (' . implode(', ', $faltan)
                . '). Usa la plantilla: ' . self::COLUMNAS_ESPERADAS . '.']);
        }

        // 3) Fase 1: se revisa el archivo entero sin escribir nada.
        $errores    = [];
        $porRevisar = [];
        $porFila    = []; // correo de entrada normalizado => número de fila

        foreach ($filas as $num => $fila) {
            if ($num === $numEnc) {
                continue; // saltar la fila de encabezados
            }

            $valores = array_values($fila);
            $datos   = [];
            foreach ($mapa as $i => $campo) {
                $datos[$campo] = trim((string) ($valores[$i] ?? ''));
            }
            if (count(array_filter($datos, fn ($v) => $v !== '')) === 0) {
                continue; // fila en blanco: se ignora sin que cuente como error
            }

            // Cada columna hereda del otro cuando viene vacía.
            $login   = $datos['email'] ?? '';
            $mostrar = $datos['correo'] ?? '';
            if ($login === '') {
                $login = $mostrar;
            }
            if ($mostrar === '') {
                $mostrar = $login;
            }

            if ($datos['name'] === '') {
                $errores[] = "Fila {$num}: falta el nombre.";
            }
            if ($datos['password'] === '') {
                $errores[] = "Fila {$num}: falta la contraseña.";
            }
            if ($login === '') {
                $errores[] = "Fila {$num}: falta el correo de entrada y el correo para mostrar.";
            } elseif (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
                $errores[] = "Fila {$num}: el correo de entrada «{$login}» no es un correo válido.";
            }
            if ($mostrar !== '' && $mostrar !== $login && !filter_var($mostrar, FILTER_VALIDATE_EMAIL)) {
                $errores[] = "Fila {$num}: el correo para mostrar «{$mostrar}» no es un correo válido.";
            }

            // El duplicado se define por el correo de entrada: es la columna con
            // índice único, o sea la única que puede romper el INSERT.
            $login = $this->normalizarCorreo($login);
            if ($login !== '') {
                if (isset($porFila[$login])) {
                    $errores[] = "Fila {$num}: el correo de entrada «{$login}» ya aparece en la fila {$porFila[$login]} de este mismo archivo.";
                } else {
                    $porFila[$login] = $num;
                }
            }

            $porRevisar[] = [
                'name'      => $datos['name'],
                'puesto'    => $datos['puesto'] ?? '',
                'planta'    => $datos['planta'] ?? '',
                'celular'   => $datos['celular'] ?? '',
                'extension' => $datos['extension'] ?? '',
                'correo'    => $mostrar,
                'email'     => $login,
                'password'  => $datos['password'],
            ];
        }

        // 4) Duplicados ya registrados en la base, en una sola consulta.
        $correos = array_keys($porFila);
        if ($correos) {
            $existentes = User::whereIn('email', $correos)->pluck('email', 'email');
            foreach ($correos as $correo) {
                if (isset($existentes[$correo])) {
                    $errores[] = "Fila {$porFila[$correo]}: el correo de entrada «{$correo}» ya está registrado.";
                }
            }
        }

        // 5) Cualquier problema cancela la importación completa.
        if ($errores) {
            return back()->withErrors(['archivo' => $this->mensajeErroresImportacion($errores)]);
        }

        if (!$porRevisar) {
            return back()->withErrors(['archivo' => 'El archivo no contiene filas de datos.']);
        }

        // 6) Fase 2: todo se inserta o no se inserta nada.
        try {
            DB::transaction(function () use ($porRevisar) {
                foreach ($porRevisar as $d) {
                    User::create([
                        'name'              => $d['name'],
                        'puesto'            => $d['puesto'],
                        'planta'            => $this->normalizarPlanta($d['planta']),
                        'celular'           => $d['celular'],
                        'extension'         => $d['extension'],
                        'correo'            => $d['correo'],
                        'email'             => $d['email'],
                        'password'          => bcrypt($d['password']),
                        'jefe'              => 0,
                        'resguardo_firmado' => 0,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            return back()->withErrors(['archivo' => 'No se pudo guardar la importación y se deshizo por completo. Vuelve a subir el archivo.']);
        }

        return back()->with('agregado', count($porRevisar) . ' usuarios importados desde Excel');
    }

    /** Devuelve el campo al que corresponde un encabezado, o null si no aplica. */
    private function campoDeEncabezado(string $llave)
    {
        if ($llave === '') {
            return null;
        }

        foreach (self::REGLAS_COLUMNAS as $regla) {
            if (in_array($llave, $regla['exacto'], true)) {
                return $regla['campo'];
            }
            foreach ($regla['contiene'] as $patron) {
                if (str_contains($llave, $patron)) {
                    return $regla['campo'];
                }
            }
        }

        return null;
    }

    private function columnasFaltantes(array $campos)
    {
        $faltan = [];
        if (!in_array('name', $campos, true)) {
            $faltan[] = 'name';
        }
        if (!in_array('password', $campos, true)) {
            $faltan[] = 'password';
        }
        if (!in_array('email', $campos, true) && !in_array('correo', $campos, true)) {
            $faltan[] = 'correo de entrada o para mostrar';
        }

        return $faltan;
    }

    /**
     * Minúsculas y sin espacios: la collation de MySQL ignora mayúsculas y
     * espacios finales, así que "Ana@x.com " y "ana@x.com" sí colisionarían
     * contra el índice único de users.email.
     */
    private function normalizarCorreo(string $correo): string
    {
        return mb_strtolower(trim($correo));
    }

    private function mensajeErroresImportacion(array $errores): string
    {
        $limite = 20;
        $total  = count($errores);
        $texto  = 'No se importó ningún usuario. Se encontraron ' . $total
            . ' problema' . ($total === 1 ? '' : 's') . ':' . "\n";
        foreach (array_slice($errores, 0, $limite) as $error) {
            $texto .= '• ' . $error . "\n";
        }
        if ($total > $limite) {
            $texto .= '• … y ' . ($total - $limite) . ' problema' . ($total - $limite === 1 ? '' : 's') . ' más.' . "\n";
        }

        return $texto . 'Corrige el archivo y vuelve a subirlo.';
    }

    public function descargar_plantilla_usuarios()
    {
        $bytes = XlsxWriter::generar([
            ['nombre', 'area', 'planta', 'telefono', 'extension', 'correo_para_mostrar', 'correo_para_entrar', 'password'],
            ['Ana Lopez', 'Sistemas', 'P1', '5550001', '101', 'ana.lopez@ejemplo.com', 'alopez@grupopabsa.com', 'CambiarEsta2026'],
            ['Luis Ruiz', 'Compras', 'P2', '5550002', '102', 'luis.ruiz@ejemplo.com', 'lruiz@grupopabsa.com', 'CambiarEsta2026'],
        ], 'Usuarios');

        $tmp = tempnam(sys_get_temp_dir(), 'plantilla');
        file_put_contents($tmp, $bytes);

        return response()->download($tmp, 'plantilla_usuarios.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }


    public function ingreso_admin(){


        $credenciales = request()->only('email', 'password');
        $remember = request('remember');




        if(request('tipo') == 'administrador' ){
            
            if(Auth::guard('admin')->attempt($credenciales, $remember)){
                request()->session()->regenerate();
                return redirect()->route('perfil.admin');            
            }
            else{
                return back()->with('error', 'Las credenciales de administrador son incorrectas');
            }

        }


        if(request('tipo') == 'soporte'){

            // Cada tipo entra contra su propia tabla: las credenciales de un
            // administrador nunca sirven para el guard 'soporte' ni al reves.
            $agente = \App\Models\AgenteSoporte::where('email', $credenciales['email'])->first();

            if($agente && !$agente->estaActivo()){
                return back()->with('error', 'Tu cuenta de agente de soporte esta desactivada, contacta al administrador.');
            }

            if(Auth::guard('soporte')->attempt($credenciales, $remember)){
                request()->session()->regenerate();
                return redirect()->route('soporte.index');
            }

            return back()->with('error', 'Las credenciales del agente de soporte no son correctas');
        }


        if(request('tipo') == 'autoriza'){

            if(Auth::guard('admin')->attempt($credenciales, $remember)){
                request()->session()->regenerate();
                return redirect()->route('perfil.autoriza_tintas');
            }

            else{
                return back()->with('error', 'Las Credenciales del Administrador * no son correctas');
            }
            
        }




        if(request('tipo') == 'usuario'){
            if(Auth::attempt($credenciales, $remember)){
                request()->session()->regenerate();
                return redirect()->route('perfil.home');            
            }
            else{
                return back()->with('error', 'Las credenciales de usuario son incorrectas');
            }
        }



        
    }





    public function dispositivos_show(){

        $user = User::find(Auth::user()->id);
        $computadoras = $user->computadoras;
        $impresoras = $user->impresoras;
        $telefonos = $user->telefonos;

        return view('user.perfil_dispositivos', compact('computadoras', 'impresoras', 'telefonos'));


    }



    public function perfil_user(){


        // $user = User::find(Auth::user()->id);
        // $computadoras = $user->computadoras;
        // $impresoras = $user->impresoras;
        // $telefonos = $user->telefonos;

        return view('user.perfil_links');
    }




    public function perfil_admin(){


        \Carbon\Carbon::setLocale('es');
        //ojala esto lo pudiera extraer en un archivo aparte
        $cantidad_computadoras = Computadora::count();
        $cantidad_impresoras = Impresora::count();
        $cantidad_telefonos = Telefono::count();
        $pedidos = Pedido::where('status', 'pendiente')->with('user')->get();
        $reportes = Reporte::all();
        $usuarios = User::all();
        $respuestas_resguardos = Resguardo::with('user')->get();

        foreach($reportes as $reporte){
            $reporte->fecha_larga = Carbon::parse($reporte->fecha_reporte)->translatedFormat('l, d \d\e F \d\e Y');
        }



        return view('admin.perfil', compact('cantidad_computadoras', 'cantidad_impresoras', 'cantidad_telefonos', 'pedidos', 'reportes', 'usuarios', 'respuestas_resguardos'));
    }


    public function cerrar_session(){
        
        Session::flush(); //Manda alv la session
        Auth::logout();
        return back();

    }


    public function eliminar_usuario($id){

        $usuario = User::findOrFail($id);
        $usuario->delete();

        return back()->with('eliminado', 'El usuario fue eliminado');




    }


    public function actualizar_usuario($id){
    
        //encontrando el usuario
        $usuario = User::findOrFail($id);
        

        //comparando, si son diferentes se aplica el cambio si no no se hace nada
        if(request('password_edit') != $usuario->password){
            $usuario->password = bcrypt(request('password_edit'));
        }


        request()->validate([
            'nombre_edit' => 'required',
            // 'celular_edit' => 'required',
            // 'email_edit' => 'required|email|unique:users,email',
            'puesto_edit' => 'required',
            'planta_edit' => 'required',
            'extension_edit' => 'nullable',
        ]);
        // No se permite dejar una encuesta marcada sin subencuetas: se bloquea todo el guardado
        $validacionSecciones = $this->seccionesValidasPorEvaluacion(
            request('evaluaciones_edit', []),
            request('secciones_edit', [])
        );

        if (count($validacionSecciones['errores']) > 0) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'secciones_edit' => $validacionSecciones['errores'],
            ]);
        }



        $usuario->name = request('nombre_edit');
        $usuario->email = request('email_edit');
        $usuario->puesto = request('puesto_edit');
        $usuario->planta = request('planta_edit');
        $usuario->correo = request('correo_edit');
        $usuario->extension = request('extension_edit');
        $usuario->celular = request('celular_edit');
        $usuario->update();

        // Si el usuario no toco el bloque de encuestas, sus asignaciones se respetan tal cual.
        // Si el campo no llega (navegador con la vista cacheada) se sincroniza igual, para no
        // fingir un guardado exitoso sin haber aplicado nada.
        $errores = [];
        $tocado = request('evaluaciones_tocadas');

        if ($tocado === null || $tocado === '1') {
            $errores = $this->sincronizarEvaluacionesUsuario($usuario, request('evaluaciones_edit', []), $validacionSecciones['secciones']);
        }

        if (count($errores) > 0) {
            return back()->with('editado', 'El usuario fue editado, pero no se asignaron las siguientes evaluaciones (faltó seleccionar secciones): ' . implode(', ', $errores));
        }

        return back()->with('editado', 'El usuario fue editado!');



    }

    private function normalizarPlanta($valor)
    {
        $valor = strtoupper(trim((string) $valor));

        if ($valor === '') {
            return '';
        }

        // Acepta P1, P2, P3, "Planta 1", "PLANTA 2", "3", 1, 2, 3...
        if (preg_match('/^(?:PLANTA\s*)?([123])$/u', $valor, $m)) {
            return 'P' . $m[1];
        }

        return $valor;
    }


    private function seccionesValidasPorEvaluacion($ids, $secciones)
    {
        $ids = is_array($ids) ? $ids : [];
        $ids = array_values(array_filter(array_map('intval', $ids)));

        $seccionesLimpias = [];
        $errores = [];

        foreach ($ids as $evaluacionId) {
            $evaluacion = Evaluacion::with('seccionesActivas')->find($evaluacionId);

            if (!$evaluacion) {
                $errores[] = 'La evaluación #' . $evaluacionId . ' ya no existe.';
                continue;
            }

            $permitidas = array_map('intval', $evaluacion->seccionesActivas->pluck('id')->all());

            $enviadas = is_array($secciones) ? ($secciones[$evaluacionId] ?? []) : [];
            $enviadas = is_array($enviadas) ? $enviadas : [$enviadas];
            $seleccionadas = array_values(array_filter(
                array_map('intval', $enviadas),
                fn($id) => in_array($id, $permitidas, true)
            ));

            if (count($seleccionadas) === 0) {
                $errores[] = 'Debes seleccionar al menos una subencuesta de "' . $evaluacion->titulo . '".';
            }

            $seccionesLimpias[$evaluacionId] = $seleccionadas;
        }

        return ['errores' => $errores, 'secciones' => $seccionesLimpias];
    }


    private function sincronizarEvaluacionesUsuario($usuario, $evaluacionesIds, $evaluacionesSecciones = [])
    {
        $ids = is_array($evaluacionesIds) ? $evaluacionesIds : explode(',', (string) $evaluacionesIds);
        $ids = array_filter(array_map('intval', $ids));

        $errores = [];

        // Activar evaluaciones seleccionadas validadas
        foreach ($ids as $evaluacionId) {
            $seleccionadas = $evaluacionesSecciones[$evaluacionId] ?? [];
            $seleccionadas = is_array($seleccionadas) ? array_filter(array_map('intval', $seleccionadas)) : [];

            if (count($seleccionadas) === 0) {
                $evaluacion = \App\Models\Evaluacion::find($evaluacionId);
                $errores[] = $evaluacion ? $evaluacion->titulo : ('Evaluación #' . $evaluacionId);
                continue;
            }

            EvaluacionUsuario::updateOrCreate(
                ['evaluacion_id' => $evaluacionId, 'usuario_id' => $usuario->id],
                ['activo' => true, 'secciones' => array_values($seleccionadas)]
            );
        }

        // Desactivar las que ya no estan seleccionadas
        EvaluacionUsuario::where('usuario_id', $usuario->id)
            ->whereNotIn('evaluacion_id', $ids)
            ->update(['activo' => false]);

        return $errores;
    }


    public function aceptar_resguardo($id){
        
        
        $usuario = User::findOrFail($id);
        $usuario->resguardo_firmado = true;
        $usuario->save();


        return back()->with('aceptado', 'Gracias por aceptar el resguardo!');
    
    }




    public function lista_dispositivos(){
    
        $computadoras = Computadora::all();
        $impresoras =   Impresora::all();
        $telefonos  = Telefono::all();


        
        return view('admin.lista_dispositivos', compact('computadoras', 'impresoras', 'telefonos'));
    }






    public function perfil_home(){
        $usuarios = User::orderBy('created_at', 'desc')->get();

        return view('user.perfil_directorio', compact('usuarios'));
    } 



    public function perfil_articles(){


        $publicaciones = Publicacion::where('categoria', 'articulos')->get();

        $tutoriales = Publicacion::where('categoria', 'tutoriales')->get();

        return view('user.articles', compact('publicaciones', 'tutoriales'));

    }


    public function configuracion(){
        $tintas = EmailSetting::obtener('tintas');
        $soporte = EmailSetting::obtener('soporte');
        return view('admin.configuracion', compact('tintas', 'soporte'));
    }

    public function config_tintas_update(){
        request()->validate([
            'mail_from' => 'required|email',
            'mail_from_name' => 'required',
            'destinatario' => 'required|email',
        ]);
        EmailSetting::updateOrCreate(
            ['seccion' => 'tintas'],
            [
                'mail_from' => request('mail_from'),
                'mail_from_name' => request('mail_from_name'),
                'destinatario' => request('destinatario'),
                'cc' => request('cc') ?: null,
            ]
        );
        return back()->with('config_guardada', 'Configuración de tintas guardada');
    }

    public function config_soporte_update(){
        request()->validate([
            'mail_from' => 'required|email',
            'mail_from_name' => 'required',
            'destinatario' => 'required|email',
        ]);
        EmailSetting::updateOrCreate(
            ['seccion' => 'soporte'],
            [
                'mail_from' => request('mail_from'),
                'mail_from_name' => request('mail_from_name'),
                'destinatario' => request('destinatario'),
                'cc' => request('cc') ?: null,
            ]
        );
        return back()->with('config_guardada', 'Configuración de soporte guardada');
    }

    public function admin_pedidos_tintas(\Illuminate\Http\Request $request){
        $aplicarFiltros = function($q) use ($request) {
            if ($request->filled('desde')) {
                $q->whereDate('fecha_pedido', '>=', $request->desde);
            }
            if ($request->filled('hasta')) {
                $q->whereDate('fecha_pedido', '<=', $request->hasta);
            }
            return $q;
        };

        $query = $aplicarFiltros(Pedido::with('user'));
        $pedidos = $query->orderBy('created_at', 'desc')->with('hijos')->withCount('hijos as residuales')->get();

        // Las tarjetas cuentan todos los pedidos: el residual es una fila
        // propia y cuenta como pedido en el conteo.
        $base = $aplicarFiltros(Pedido::with('user'));
        $totalPedidos = (clone $base)->count();
        $completados = (clone $base)->where('status', 'completado')->count();
        $parciales = (clone $base)->where('status', 'Entrega parcial')->count();
        $pendientes = (clone $base)->where(function ($q) {
            // Los status nulos se muestran como pendientes en la tabla, asi
            // que el contador no puede discrepar con lo que se ve.
            $q->where('status', 'pendiente')->orWhereNull('status');
        })->count();
        $porcentajeCumplimiento = $totalPedidos > 0 ? round(($completados / $totalPedidos) * 100) : null;

        $topUsuarios = (clone $base)
            ->selectRaw('user_id, count(*) as n')
            ->groupBy('user_id')
            ->orderByDesc('n')
            ->limit(5)
            ->get();
        $topUsuarios->load('user');

        return view('admin.pedidos_tintas', compact(
            'pedidos', 'totalPedidos', 'completados', 'parciales', 'pendientes', 'porcentajeCumplimiento', 'topUsuarios'
        ));
    }

    public function admin_pedido_completar($id)
    {
        $pedido = Pedido::findOrFail($id);

        $r = EntregaPedido::marcar($pedido, request()->all(), Auth::guard('admin')->user()->nombre);

        if (isset($r['aviso'])) {
            return back()->with('completado', $r['aviso']);
        }

        if (! $r['ok']) {
            return back()->withErrors($r['errores'])->withInput();
        }

        if (isset($r['correo']) && ! $r['correo']['ok'] && $r['correo']['intentos'] > 0) {
            session()->flash('correo_fallido', $r['correo']['error']);
        }

        return back()->with('completado', $r['mensaje']);
    }

    public function admin_soporte(\Illuminate\Http\Request $request){
        return view('admin.soporte', array_merge($this->datosListadoSoporte($request), [
            'rutaLista' => 'admin.soporte',
            'rutaExportar' => 'admin.soporte.exportar',
            'rutaDetalle' => 'detalle.reporte.admin',
            'navView' => 'assets.nav',
        ]));
    }

    /**
     * Listado de solicitudes para el agente de soporte: misma consulta y misma
     * vista que el admin, solo cambian las rutas y el menu.
     */
    public function soporte_index(\Illuminate\Http\Request $request)
    {
        return view('admin.soporte', array_merge($this->datosListadoSoporte($request), [
            'rutaLista' => 'soporte.index',
            'rutaExportar' => 'soporte.exportar',
            'rutaDetalle' => 'soporte.detalle',
            'navView' => 'assets.nav_soporte',
        ]));
    }

    /**
     * Datos del listado de soporte, compartidos por el panel y el agente.
     */
    private function datosListadoSoporte($request)
    {
        \Carbon\Carbon::setLocale('es');

        $aplicarFiltros = $this->aplicarFiltrosSoporte($request);

        $query = $aplicarFiltros(Reporte::with(['user', 'adjuntos']));
        $reportes = $query->orderBy('created_at', 'desc')->paginate(25)->withQueryString();
        foreach($reportes as $reporte){
            $reporte->fecha_larga = \Carbon\Carbon::parse($reporte->fecha_reporte)->translatedFormat('l, d \\d\\e F \\d\\e Y');
        }

        $baseStats = $aplicarFiltros(Reporte::query());
        $totalReportes = (clone $baseStats)->count();
        $totalEvaluados = (clone $baseStats)->whereNotNull('calificacion')->count();
        $promedioRaw = (clone $baseStats)->whereNotNull('calificacion')->avg('calificacion');
        $promedio = $promedioRaw !== null ? round($promedioRaw, 2) : null;

        $distribucionRaw = (clone $baseStats)
            ->whereNotNull('calificacion')
            ->selectRaw('calificacion as estrella, count(*) as n')
            ->groupBy('calificacion')
            ->pluck('n', 'estrella')
            ->toArray();
        $distribucion = [];
        for ($i = 1; $i <= 5; $i++) {
            $distribucion[$i] = $distribucionRaw[$i] ?? 0;
        }

        // Los pendientes también cuentan los status nulos: la vista los muestra
        // como pendientes, asi que el contador no puede discrepar con la tabla.
        $completados = (clone $baseStats)->where('status', 'completado')->count();
        $pendientes = (clone $baseStats)
            ->where(function ($q) {
                $q->where('status', 'pendiente')->orWhereNull('status');
            })
            ->count();

        $usuarios = User::whereHas('reportes')->orderBy('name')->get();
        $dispositivos = Reporte::distinct()->pluck('dispositivo')->filter()->sort()->values();

        return compact(
            'reportes', 'totalReportes', 'totalEvaluados', 'promedio', 'distribucion',
            'completados', 'pendientes', 'usuarios', 'dispositivos'
        );
    }

    /**
     * Filtros compartidos entre el listado y la exportacion a PDF, para que el
     * archivo descargado sea exactamente lo que se esta viendo en pantalla.
     */
    private function aplicarFiltrosSoporte($request)
    {
        return function ($q) use ($request) {
            if ($request->filled('usuario')) {
                $q->where('user_id', $request->usuario);
            }
            if ($request->filled('dispositivo')) {
                $q->where('dispositivo', $request->dispositivo);
            }
            if ($request->filled('desde')) {
                $q->whereDate('fecha_reporte', '>=', $request->desde);
            }
            if ($request->filled('hasta')) {
                $q->whereDate('fecha_reporte', '<=', $request->hasta);
            }
            if ($request->filled('estrellas')) {
                $q->where('calificacion', $request->estrellas);
            }
            if ($request->filled('estado')) {
                if ($request->estado === 'pendiente') {
                    $q->where(function ($w) {
                        $w->where('status', 'pendiente')->orWhereNull('status');
                    });
                } else {
                    $q->where('status', $request->estado);
                }
            }
            return $q;
        };
    }

    public function admin_soporte_exportar(\Illuminate\Http\Request $request)
    {
        return $this->exportarSoportePdf($request);
    }

    /**
     * Exportacion a PDF del agente de soporte: mismo archivo que el admin.
     */
    public function soporte_exportar(\Illuminate\Http\Request $request)
    {
        return $this->exportarSoportePdf($request);
    }

    /**
     * Exporta a PDF las solicitudes que estan en pantalla, con su calificacion,
     * observaciones y seguimiento.
     */
    private function exportarSoportePdf($request)
    {
        \Carbon\Carbon::setLocale('es');

        // dompdf se atraganta con miles de filas: mejor avisar que colgarse.
        $limite = (int) config('soporte.export_limite', 1000);
        $aplicarFiltros = $this->aplicarFiltrosSoporte($request);
        $total = $aplicarFiltros(Reporte::query())->count();

        if ($total > $limite) {
            return back()->with('error', 'Los filtros seleccionados devuelven ' . $total
                . ' solicitudes. Acorta el filtro (por ejemplo, por fechas) para poder exportar: el maximo es ' . $limite . '.');
        }

        $reportes = $aplicarFiltros(Reporte::with(['user', 'seguimientos', 'adjuntos']))
            ->orderBy('created_at', 'desc')
            ->get();

        $baseStats = $aplicarFiltros(Reporte::query());
        $totalEvaluados = (clone $baseStats)->whereNotNull('calificacion')->count();
        $promedioRaw = (clone $baseStats)->whereNotNull('calificacion')->avg('calificacion');
        $promedio = $promedioRaw !== null ? round($promedioRaw, 2) : null;
        $completados = (clone $baseStats)->where('status', 'completado')->count();
        $pendientes = (clone $baseStats)
            ->where(function ($q) {
                $q->where('status', 'pendiente')->orWhereNull('status');
            })
            ->count();

        $distribucionRaw = (clone $baseStats)
            ->whereNotNull('calificacion')
            ->selectRaw('calificacion as estrella, count(*) as n')
            ->groupBy('calificacion')
            ->pluck('n', 'estrella')
            ->toArray();
        $distribucion = [];
        for ($i = 1; $i <= 5; $i++) {
            $distribucion[$i] = $distribucionRaw[$i] ?? 0;
        }

        $html = view('admin.soporte_pdf', [
            'reportes' => $reportes,
            'totalReportes' => $total,
            'totalEvaluados' => $totalEvaluados,
            'promedio' => $promedio,
            'distribucion' => $distribucion,
            'completados' => $completados,
            'pendientes' => $pendientes,
            'filtros' => $this->filtrosSoporteTexto($request),
            'generado' => \Carbon\Carbon::now()->translatedFormat('l, d \\d\\e F \\d\\e Y \\a \\l\\a\\s H:i'),
        ])->render();

        $nombre = 'soporte_' . \Carbon\Carbon::now()->format('Ymd_Hi') . '.pdf';

        return \App\Services\PdfGenerator::download($html, $nombre);
    }

    /**
     * Traduce los filtros aplicados a texto para poder imprimirlos en el PDF.
     */
    private function filtrosSoporteTexto($request)
    {
        $texto = [];
        if ($request->filled('estado')) {
            $texto[] = 'Estado: ' . ($request->estado === 'completado' ? 'Completados' : 'Pendientes');
        }
        if ($request->filled('usuario')) {
            $usuario = User::find($request->usuario);
            $texto[] = 'Usuario: ' . ($usuario->name ?? ' eliminados #' . $request->usuario);
        }
        if ($request->filled('dispositivo')) {
            $texto[] = 'Dispositivo: ' . $request->dispositivo;
        }
        if ($request->filled('desde')) {
            $texto[] = 'Desde: ' . $request->desde;
        }
        if ($request->filled('hasta')) {
            $texto[] = 'Hasta: ' . $request->hasta;
        }
        if ($request->filled('estrellas')) {
            $texto[] = 'Calificacion: ' . $request->estrellas . ' estrella(s)';
        }

        return count($texto) > 0 ? $texto : ['Sin filtros (todas las solicitudes)'];
    }

    /**
     * Cierra solo la sesion del guard soporte y manda al login. No toca la del
     * guard web, asi que un agente con sesion de usuario no se ve afectado.
     */
    public function soporte_cerrar_sesion()
    {
        Auth::guard('soporte')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Sesion de agente de soporte cerrada.');
    }
}
