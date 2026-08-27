<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Impresora;
use Illuminate\Http\Request;

class printerController extends Controller
{

    public function store(){

        $usuarios = User::all();
        return view('admin.add_printer', compact('usuarios'));

    }


    public function create(){



        //validando los datos
        request()-> validate([
            'marca' => 'required',
            'usuario' => 'required',
            'modelo' => 'required',
            'comparte' => 'required',
            'tipo' => 'required',
            'estado' => 'required', 
            'serie' => 'required',
            'observaciones' => 'required',
        ]);
        //validando los datos



        $imagen1 = '';
        $imagen2 = '';
        $imagen3 = '';

        if(request()->hasFile('imagen1')){
            $imagen1 = request()->file('imagen1')->store('public');
        }

        if(request()->hasFile('imagen2')){
            $imagen2 = request()->file('imagen2')->store('public');
        }


        
        //Agregando los datos a la BD   
        Impresora::create([
            'user_id' => request('usuario'),
            'marca' => request('marca'),
            'modelo' => request('modelo'),
            'tipo' => request('tipo'),
            'estado' =>request('estado'),
            'comparte' => request('comparte'),
            'serie' => request('serie'),
            'observaciones' => request('observaciones'),
            'imagen1' => $imagen1,
            'imagen2' => $imagen2
        ]);
        //Agregando los datos a la BD


        return redirect()->route('add_printer')->with('agregado', 'Impresora agregada');

   


    }

    public function show(){
        return view('resultados.printer');
    }




    public function lista_impresoras(){

        $impresoras = Impresora::all();

        return view('admin.dispositivos.impresoras', compact('impresoras'));

    }




    public function fotos_impresora_usuario($id){

        $impresora = Impresora::findOrFail($id);

        $imagen1= $impresora->imagen1;
        $imagen2= $impresora->imagen2;



        if(request()->hasFile('imagen1')){
            $imagen1 = request()->file('imagen1')->store('public');
        }
        if(request()->hasFile('imagen2')){
            $imagen2 = request()->file('imagen2')->store('public');
        }


        $impresora->imagen1 = $imagen1;
        $impresora->imagen2 = $imagen2;


        $impresora->update();



        return back()->with('fotos_subidas', 'Muchas gracias');



    }



    public function editar_impresora_show($id){
        
        $impresora = Impresora::findOrFail($id);
        $usuarios = User::all();

        return view('admin.dispositivos.editar_impresora', compact('impresora', 'usuarios'));
        

    }

    public function editar_impresora_update($id){
    
        $imagen0 = "";
        $imagen1= "" ;


        if(request()->hasFile('imagen0')){
            $imagen0 = request('imagen0')->store('public');
        }



        if(request()->hasFile('imagen1')){
            $imagen1 = request('imagen1')->store('public');
        }





        $impresora = Impresora::findOrFail($id);

        $impresora->user_id = request('usuario');
        $impresora->comparte = request('comparte');
        $impresora->estado = request('estado');
        $impresora->modelo = request('modelo');
        $impresora->marca = request('marca');
        $impresora->serie = request('serie');
        $impresora->tipo = request('tipo');
        $impresora->observaciones = request('observaciones');
        $impresora->imagen1 = $imagen0;
        $impresora->imagen2 = $imagen1;


        $impresora->update();

        return back()->with('actualizado', 'La impresora fue actualizada');

    }








}
