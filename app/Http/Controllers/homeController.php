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

        return view('inicio');  
    }









}
