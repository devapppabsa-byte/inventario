<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class directorioController extends Controller
{

    public function show(){

        $usuarios = User::orderBy('created_at', 'desc')->get();

        return view('user.perfil_directorio', compact('usuarios'));

    }
}
