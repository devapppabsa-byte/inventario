<?php

namespace App\Http\Controllers;

use BD;
use DB;
use Carbon\Carbon;
use App\Models\Pedido;
use App\Models\CorreoEnvio;
use App\Services\NotificadorCorreo;
use App\Services\EntregaPedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class tintasController extends Controller
{
    /**
     * Tope de la foto de evidencia, en kilobytes, que es la unidad que usa la
     * regla `max:`. Vive aqui para que la regla, el mensaje de error y el
     * letrero de la vista no puedan quedar desincronizados entre si, igual que
     * hace ticketsController con su limitesAdjuntos().
     *
     * El maximo real lo impone la plataforma, no la app: Vercel rechaza con
     * 413 cualquier cuerpo mayor a 4.5 MB. Antes se declaraban 20 MB, que no
     * se podian alcanzar en ningun entorno. Las imagenes llegan reducidas
     * desde el navegador, asi que 4 MB de sobra para una foto de evidencia.
     */
    public const MAX_FOTO_KB = 4096;

    public function show(){

        $pedidos = Pedido::where('user_id', Auth::user()->id)->with('hijos')->orderBy('created_at', 'desc')->simplePaginate(7);

        $reenviables = CorreoEnvio::idsConUltimoFallo(
            Pedido::class,
            $pedidos->pluck('id')->all()
        );

        return view('user.perfil_tintas', [
            'pedidos' => $pedidos,
            'maxFotoMb' => round(self::MAX_FOTO_KB / 1024, 1),
            'reenviables' => $reenviables,
        ]);


    }

    
    public function pedido(){

        request()->validate([
            'foto_tintas' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:' . self::MAX_FOTO_KB . '|dimensions:min_width=300,min_height=300'
        ], [
            'foto_tintas.image'      => 'La foto debe ser una imagen. No se aceptan documentos ni archivos de otro tipo.',
            'foto_tintas.mimes'      => 'La foto debe ser JPG, PNG, GIF o WEBP.',
            'foto_tintas.max'        => 'La foto supera el maximo de ' . round(self::MAX_FOTO_KB / 1024, 1) . ' MB.',
            'foto_tintas.dimensions' => 'La foto debe medir al menos 300×300 px.',
        ]);
        $foto_tintas = request()->file('foto_tintas')->store('public');

        
        if(request('numero') == 'Cintas'){

            request()->validate([
                'numero' => 'required',
                'cantidad_cintas' => 'required',
               ]);

               $colores = "[".request('cantidad_cintas')."]";
       
        }


        if(request('numero') == 'Otra'){

            request()->validate([
                'numero' => 'required',
                'marca' => 'required',
                'checkboxes' => 'required|array|min:1'
               ]);


             $colores = json_encode(request('checkboxes'));

        }



        if(request('numero') != 'Otra'  && request('numero') != 'Cintas'){
            
            request()->validate([
             'checkboxes' => 'required|array|min:1',
             'numero' => 'required'
            ]);
    
            //convirtiendo los datos a JSON para poder meter el array en la bd    
            $colores = json_encode(request('checkboxes'));
        }


        $fecha_hoy = substr(Carbon::now(), 0, 10);

   

        //Guardando los datos en la bd
        Pedido::create([
            'numero' => request('numero'),
            'colores' =>$colores, 
            'fecha_pedido' => $fecha_hoy,
            'user_id' => Auth::user()->id,
            'marca' => request('marca'),
            'cantidad' => request('cantidad'),
            'foto_tintas' => $foto_tintas
        ]);

        $pedido = Pedido::where('user_id', Auth::user()->id)->latest()->first();

        // Igual que en soporte: el pedido ya esta guardado y el aviso a
        // recepcion no puede convertir un fallo de red en un error de peticion.
        if ($pedido) {
            $envio = NotificadorCorreo::tinta($pedido);
            if (! $envio['ok'] && $envio['intentos'] > 0) {
                session()->flash('correo_fallido', $envio['error']);
            }
        }

        return back()->with('pedido_enviado', 'El pedido fue enviado!');


        
    }

    /**
     * Reenvia el aviso de un pedido ya guardado. Solo puede hacerlo quien lo
     * pidio, y un fallo de SMTP nunca se convierte en un 500: se audita y se avisa.
     */
    public function reenviar_pedido($id)
    {
        $pedido = Pedido::where('user_id', Auth::user()->id)
            ->findOrFail($id);

        $envio = NotificadorCorreo::tinta($pedido);

        if (! $envio['ok']) {
            session()->flash('correo_fallido', $envio['error'] ?: 'No se pudo contactar el servidor de correo.');
            return back()->with('reenvio_fallido', 'El pedido sigue guardado, pero el aviso por correo falló.');
        }

        return back()->with('reenviado', 'El aviso fue reenviado correctamente.');
    }


    public function respuesta_admin($id){

        
        $pedido = Pedido::findOrFail($id);
        $pedido->respuesta_admin = request('respuesta_admin');
        $pedido->update();

        return back()->with('respuesta', 'La respuesta fue enviada');


    }







    public function pedido_completo($id){

        $pedido = Pedido::findOrFail($id);

        // Solo el dueño puede completar su pedido. La vista lista los
        // propios, pero la ruta acepta cualquier id.
        abort_unless((int) $pedido->user_id === (int) Auth::id(), 403);

        $r = EntregaPedido::marcar($pedido, request()->all(), Auth::user()->name);

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


    //metodo que me ayuda a que se autoricen las tintas, a parti de aqui se va a escribir ese codigo


    public function autorizar_tintas(){

        $pedidos = Pedido::with('user')->where('status', 'pendiente')->get();

        return view('admin.perfil_consejo_directivo', compact('pedidos'));
    }



}
