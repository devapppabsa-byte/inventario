<?php

namespace App\Http\Controllers;

use App\Models\Impresora;
use App\Models\Regulador;
use App\Models\Computadora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class homeController extends Controller
{
    public function index(){

        // Un agente con sesion abierta no tiene nada que hacer en el login.
        if (auth('soporte')->check()) {
            return redirect()->route('soporte.index');
        }

        return view('inicio');  
    }









}
