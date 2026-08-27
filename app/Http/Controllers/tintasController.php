<?php

namespace App\Http\Controllers;

use BD;
use DB;
use Carbon\Carbon;
use App\Models\Pedido;
use App\Models\EmailSetting;
use App\Mail\PedidoTinta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class tintasController extends Controller
{
    
    public function show(){

        $pedidos = Pedido::where('user_id', Auth::user()->id)->orderBy('created_at', 'desc')->simplePaginate(7);

        return view('user.perfil_tintas', compact('pedidos'));


    }

    
    public function pedido(){


        request()->validate([
            'foto_tintas' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120'
        ]);
        $foto_tintas = request()->file('foto_tintas')->store('Public');

        
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
        $setting = EmailSetting::obtener('tintas');
        if ($setting && $pedido) {
            $mail = Mail::to($setting->destinatario)
                ->send(new PedidoTinta($pedido, $setting->mail_from, $setting->mail_from_name));
            if (!empty($setting->cc)) {
                $ccs = array_map('trim', explode(',', $setting->cc));
                Mail::to($setting->destinatario)
                    ->cc($ccs)
                    ->send(new PedidoTinta($pedido, $setting->mail_from, $setting->mail_from_name));
            }
        }

        return back()->with('pedido_enviado', 'El pedido fue enviado!');


        
    }


    public function respuesta_admin($id){

        
        $pedido = Pedido::findOrFail($id);
        $pedido->respuesta_admin = request('respuesta_admin');
        $pedido->update();

        return back()->with('respuesta', 'La respuesta fue enviada');


    }







    public function pedido_completo($id){

        $pedido = Pedido::findOrFail($id);

        $pedido->fecha_entrega = substr(Carbon::now(), 0, 11);
        $pedido->status = 'Completado';

        $pedido->update();


        return back()->with('completado', 'Se marco el pedido como completado');


    }


    //metodo que me ayuda a que se autoricen las tintas, a parti de aqui se va a escribir ese codigo


    public function autorizar_tintas(){

        $pedidos = Pedido::with('user')->where('status', 'pendiente')->get();

        return view('admin.perfil_consejo_directivo', compact('pedidos'));
    }



}
