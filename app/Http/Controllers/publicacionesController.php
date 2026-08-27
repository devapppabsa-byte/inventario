<?php

namespace App\Http\Controllers;

use App\Models\Visita;
use App\Models\Publicacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class publicacionesController extends Controller
{
    
    public function show(){

        $publicaciones = Publicacion::all();
        $visitas = Visita::with('user')->get();
        
        return view('admin.gestionar_publicaciones', compact('publicaciones', 'visitas'));


    }

    public function agregar_post(){

        return view('admin.agregar_post');

    }


    public function post_store(){

        if(request()->hasFile('portada')){
            $portada = request()->file('portada')->store('public');
        }


        request()->validate([
            'titulo' => 'required',
            'introduccion' => 'required',
            'cuerpo' => 'required',
            'portada' => 'required',
            'categoria' => 'required',
        ]);
    
        $publicacion = new Publicacion();

        $publicacion->titulo = request('titulo');
        $publicacion->introduccion = request('introduccion'); 
        $publicacion->cuerpo = request('cuerpo');
        $publicacion->portada = $portada;
        $publicacion->categoria = request('categoria');
        $publicacion->autor = Auth::guard("admin")->user()->nombre;

        $publicacion->save();



        return back()->with('publicacion_agregada', 'Publicacion agregada correctamente');
    }


    public function mostrar_post($id){



        $id_user = Auth()->user()->id;

        $visita = new Visita();

        $visita->user_id = $id_user;
        $visita->publicacion_id = $id;

        $visita->save();


        //contador de los likes 
       $contador_likes = DB::table('reacciones')->where('publicacion_id', $id)->where('reaccion', 'like')->count();
       $contador_dislikes = DB::table('reacciones')->where('publicacion_id', $id)->where('reaccion', 'dislike')->count();
       $contador_loveit = DB::table('reacciones')->where('publicacion_id', $id)->where('reaccion', 'loveit')->count();

       $reaccion = DB::table('reacciones')->where('publicacion_id', $id)->where('user_id', Auth::user()->id)->get();

       $publicacion = Publicacion::with('comentarios.user')->find($id);
       
       
    

       return view('user.perfil_post', compact('publicacion', 'contador_likes', 'contador_dislikes', 'contador_loveit', 'reaccion'));


    }





}
