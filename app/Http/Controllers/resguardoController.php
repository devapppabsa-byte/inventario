<?php

namespace App\Http\Controllers;

use App\Models\Resguardo;
use App\Models\User;
use App\Models\Telefono;
use App\Models\Impresora;
use App\Models\Computadora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class resguardoController extends Controller
{
    public function show(){
        
       $usuario = User::findOrFail(Auth::user()->id); 
       $computadoras = $usuario->computadoras;
       $impresoras = $usuario->impresoras;
       $telefonos = $usuario->telefonos;
       $resguardo = $usuario->resguardos;


       return view('assets.resguardo', compact('computadoras', 'impresoras', 'telefonos', 'usuario'));


    }


    public function observaciones(){

        request()->validate([
            'observaciones' => 'required'
        ]);
        
        $resguardo = new Resguardo();
        $resguardo->user_id = request('id');
        $resguardo->observaciones = request('observaciones');
        $resguardo->save();
        return back()->with('observaciones',  'Las observaciones fueron enviadas!');


    }


    // public function confirmar(){
        
    //     $resguardo = new Resguardo();
    //     $resguardo->user_id = request('id_user');
    //     $resguardo->aceptado = true;
    //     $resguardo->$resguardo;
    //     $resguardo->save();

    //     return back()->with('aceptado', 'Gracias por aceptar le resguardo!');

    // }


    public function show_admin($id){
        
        $usuario = User::findOrFail($id); 
        $computadoras = $usuario->computadoras;
        $impresoras = $usuario->impresoras;
        $telefonos = $usuario->telefonos;
        $resguardo = $usuario->resguardos;
 
 
        return view('admin.resguardo_admin', compact('computadoras', 'impresoras', 'telefonos', 'usuario'));
 
 
     }



}
