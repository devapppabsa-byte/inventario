<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Computadora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class pcController extends Controller
{
    public function store(){

        $usuarios = User::all();
        return view('admin.add_pc', compact('usuarios'));
        
    }


    public function create(){
        
        request()->validate([
            'marca' => 'required',
            'modelo' => 'required',
            'so' => 'required',
            'usuario' => 'required',
            'numero_serie' => 'required',
            'size_hdd' => 'required',
            'procesador' => 'required',
            'size_ssd' => 'required',
            'size_ram' => 'required',
            'estado' => 'required',
            'accesorios' => 'required',
            'observaciones' => 'required'

        ]);
        





        $imagen1='';
        $imagen2='';
        $imagen3='';


        if(request()->hasFile('imagen1')){
            $imagen1 = request()->file('imagen1')->store('public');
        }
        if(request()->hasFile('imagen2')){
            $imagen2 = request()->file('imagen2')->store('public');
        }
        if(request()->hasFile('imagen3')){
            $imagen3 = request()->file('imagen3')->store('public');
        }


        

        $pc = new Computadora;

        $pc->marca = request('marca');
        $pc->tipo = request('tipo');
        $pc->modelo = request('modelo');
        $pc->numero_serie = request('numero_serie');
        $pc->size_hdd = request('size_hdd');
        $pc->size_ssd = request('size_ssd');
        $pc->SO = request('so');
        $pc->ram = request('size_ram');
        $pc->procesador = request('procesador');
        $pc->estado = request('estado');
        $pc->user_id = request('usuario');
        $pc->observaciones = request('observaciones');
        $pc->accesorios = request('accesorios');
        $pc->imagen1 =  $imagen1;
        $pc->imagen2 = $imagen2 ;
        $pc->imagen3 = $imagen3 ;

        $pc->save();

        return redirect()->route('add_pc')->with('agregada', 'Computadora agregada');

    }





    public function fotos_computadora_usuario($id){





        $computadora = Computadora::findOrFail($id);

        $imagen1= $computadora->imagen1;
        $imagen2= $computadora->imagen2;
        $imagen3= $computadora->imagen3;


        if(request()->hasFile('imagen1')){
            $imagen1 = request()->file('imagen1')->store('public');
        }
        if(request()->hasFile('imagen2')){
            $imagen2 = request()->file('imagen2')->store('public');
        }
        if(request()->hasFile('imagen3')){
            $imagen3 = request()->file('imagen3')->store('public');
        }


        $computadora->imagen1 = $imagen1;
        $computadora->imagen2 = $imagen2;
        $computadora->imagen3 = $imagen3;

        $computadora->update();

        return back()->with('fotos_subidas', 'Muchas gracias');


    }





    // public function show(){
    //     return view('resultados.pc');
    // }



    public function lista_computadoras(){

        $computadoras = Computadora::all();

        return view('admin.dispositivos.computadoras', compact('computadoras'));
    }






    public function editar_computadora_show($id){

        $computadora = Computadora::findOrFail($id);
        $usuarios = User::all();


        return view('admin.dispositivos.editar_computadora', compact('computadora', 'usuarios'));

    }


    public function editar_computadora_update($id){

        $imagen1="";
        $imagen2="";
        $imagen3="";


        

        if(request()->hasFile('imagen1')){
            $imagen1 = request()->file('imagen1')->store('public');
        }
        if(request()->hasFile('imagen2')){
            $imagen2 = request()->file('imagen2')->store('public');
        }

        if(request()->hasFile('imagen3')){
            $imagen3 = request()->file('imagen3')->store('public');
        }




        request()->validate([




        ]);



        $computadora = Computadora::findOrFail($id);

        $computadora->procesador = request('procesador');
        $computadora->so = request('so');
        $computadora->user_id = request('usuario');
        $computadora->size_hdd = request('size_hdd');
        $computadora->size_ssd = request('size_ssd');
        $computadora->ram = request('ram');
        $computadora->observaciones = request('observaciones');
        $computadora->accesorios = request('accesorios');
        $computadora->imagen1 = $imagen1;
        $computadora->imagen2 = $imagen2;
        $computadora->imagen3 = $imagen3;
        
        $computadora->update();
    

        return back()->with('actualizado', 'Los datos fueron actualizados');

    }













}
