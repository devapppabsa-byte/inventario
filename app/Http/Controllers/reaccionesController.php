<?php

namespace App\Http\Controllers;

use App\Models\Reaccion;
use App\Models\Publicacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class reaccionesController extends Controller
{
    

    public function reaccion_store(Request $request){
        
        $user_id = request('user_id');
        $publicacion_id = request('publicacion_id');
        $reaccion_nueva = request('reaccion');

        

        //verificar si ya existe el registro
       $reaccion = DB::select("SELECT*FROM reacciones WHERE user_id LIKE $user_id AND publicacion_id LIKE $publicacion_id");
        

        if($reaccion){
            
            $actualiza_reaccion = Reaccion::findOrFail($reaccion[0]->id);
            $actualiza_reaccion->reaccion = $reaccion_nueva;
            $actualiza_reaccion->update();
            
            return back();

        }

        else{

            $nueva_reaccion = new Reaccion();
            $nueva_reaccion->reaccion = $reaccion_nueva;
            $nueva_reaccion->user_id = $user_id;
            $nueva_reaccion->publicacion_id = $publicacion_id;
            $nueva_reaccion->save();
            
            return back();
        }

        //contando las reacciones despues  de todo el proceso
  

        }
        


        








    

    }


