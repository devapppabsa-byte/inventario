<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Telefono;
use Illuminate\Http\Request;

class telefonoController extends Controller
{
    public function store(){

        $usuarios = User::all();

        return view('admin.add_telefono', compact('usuarios')) ;
    }






    public function create(){



        request()->validate([

            'marca' => 'required',
            'modelo' => 'required',
            'serie' => 'required',
            'observaciones' => 'required',
            'comparte' => 'required',
            'tipo' => 'required',
            'estado' => 'required',
            'estado' => 'required',
            'usuario' => 'required'

        ]);



        $imagen1 = '';
        $imagen2 = '';
        $imagen3 = '';



        if(request()->hasFile('imagen1')){
            $imagen1 = request()->file('imagen1')->store('public');
        }


        if(request()->hasFile('imagen2')){
            $imagen2 = request()->file('imagen2')->store('public');
        }

        
        if(request()->hasFile('imagen3')){
            $imagen3 = request()->file('imagen3')->store('public');
        }




        Telefono::create([
            'marca' => request('marca'),
            'user_id' => request('usuario'),
            'modelo' => request('modelo'),
            'serie' => request('serie'),
            'tipo' => request('tipo'),
            'comparte' => request('comparte'),
            'estado' =>request('estado'),
            'observaciones' => request('observaciones'),
            'imagen1' => $imagen1,
            'imagen2' => $imagen2,
            'imagen3' => $imagen3
        ]);


        return redirect()->route('add.telefono')->with('agregado', 'El telefono fue agregado');
    }



     public function show(){
         return view('resultados.ups');
     }


     public function lista_telefonos(){

        $telefonos = Telefono::all();
        return view('admin.dispositivos.telefonos', compact('telefonos'));

     }





     public function fotos_telefono_usuario($id){

        $telefono = Telefono::findOrFail($id);

        $imagen1= $telefono->imagen1;
        $imagen2= $telefono->imagen2;
        $imagen3= $telefono->imagen3;



        if(request()->hasFile('imagen1')){
            $imagen1 = request()->file('imagen1')->store('public');
        }
        if(request()->hasFile('imagen2')){
            $imagen2 = request()->file('imagen2')->store('public');
        }
        if(request()->hasFile('imagen3')){
            $imagen3 = request()->file('imagen3')->store('public');
        }


        $telefono->imagen1 = $imagen1;
        $telefono->imagen2 = $imagen2;
        $telefono->imagen3 = $imagen3;


        $telefono->update();



        return back()->with('fotos_subidas', 'Muchas gracias');






    }



    public function editar_telefono_show($id){
   
         $telefono = Telefono::findOrFail($id);
         $usuarios = User::all();
            
         return view('admin.dispositivos.editar_telefono', compact('telefono', 'usuarios'));
   
    }



    public function editar_telefono_update($id){

        $imagen1 = '';
        $imagen2 = '';
        $imagen3 = '';

        if(request()->hasFile('imagen1')){
            $imagen1 = request('imagen1');
        }

        if(request()->hasFile('imagen2')){
            $imagen2 = request('imagen2');
        }

        if(request()->hasFile('imagen3')){
            $imagen3 = request('imagen3');
        }

        request()->validate([

            'usuario' => 'required',
            'marca' => 'required',
            'modelo' => 'required',
            'serie' => 'required',
            'comparte' => 'required',
            'tipo' => 'required',
            'estado' => 'required',
            'observaciones' => 'required'

        ]);

        
 
        $telefono = Telefono::findOrFail($id);

        $telefono->user_id = request('usuario');
        $telefono->marca = request('marca');
        $telefono->modelo = request('modelo');
        $telefono->serie = request('serie');
        $telefono->comparte = request('comparte');
        $telefono->tipo = request('tipo');
        $telefono->nuevo = request('estado');
        $telefono->observaciones = request('observaciones');

        $telefono->save();


        return back()->with('actualizado', 'Se actualizaron los datos del teléfono');

    }



}
