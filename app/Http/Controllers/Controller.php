<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Pedido;
use App\Models\Reporte;
use App\Models\PostUser;
use App\Models\Telefono;
use App\Models\Impresora;
use App\Models\Resguardo;
use App\Models\Computadora;
use App\Models\Publicacion;
use App\Models\EmailSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;



    public function show_formulario(){

        $usuarios = User::all();

        return view('admin.agregar_usuarios', compact('usuarios'));
    }



    public function registrar_usuarios(){

    
        request()->validate([
            'nombre' => 'required',
            'email' => 'required|email|unique:users,correo',
            'puesto' => 'required',
            'correo' => 'required|email|',
            'password' => 'required',
            'planta' => 'required',
            'jefe' => 'required',
            'extension' => 'required',
        ]);



        $usuario = new  User();

        $usuario->name = request('nombre');
        $usuario->email = request('email');
        $usuario->puesto = request('puesto');
        $usuario->planta = request('planta');
        $usuario->jefe = request('jefe');
        $usuario->extension = request('extension');
        $usuario->correo = request('correo');
        $usuario->celular = request('celular');
        $usuario->password = bcrypt(request('password'));
        $usuario->save();

        return back()->with('agregado', 'El usuario fue agregado');

    }



    public function ingreso_admin(){


        $credenciales = request()->only('email', 'password');
        $remember = request('remember');




        if(request('tipo') == 'administrador' ){
            
            if(Auth::guard('admin')->attempt($credenciales, $remember)){
                request()->session()->regenerate();
                return redirect()->route('perfil.admin');            
            }
            else{
                return back()->with('error', 'Las credenciales de administrador son incorrectas');
            }

        }


        if(request('tipo') == 'autoriza'){

            if(Auth::guard('admin')->attempt($credenciales, $remember)){
                request()->session()->regenerate();
                return redirect()->route('perfil.autoriza_tintas');
            }

            else{
                return back()->with('error', 'Las Credenciales del Administrador * no son correctas');
            }
            
        }




        if(request('tipo') == 'usuario'){
            if(Auth::attempt($credenciales, $remember)){
                request()->session()->regenerate();
                return redirect()->route('perfil.home');            
            }
            else{
                return back()->with('error', 'Las credenciales de usuario son incorrectas');
            }
        }



        
    }





    public function dispositivos_show(){

        $user = User::find(Auth::user()->id);
        $computadoras = $user->computadoras;
        $impresoras = $user->impresoras;
        $telefonos = $user->telefonos;

        return view('user.perfil_dispositivos', compact('computadoras', 'impresoras', 'telefonos'));


    }



    public function perfil_user(){


        // $user = User::find(Auth::user()->id);
        // $computadoras = $user->computadoras;
        // $impresoras = $user->impresoras;
        // $telefonos = $user->telefonos;

        return view('user.perfil_links');
    }




    public function perfil_admin(){


        \Carbon\Carbon::setLocale('es');
        //ojala esto lo pudiera extraer en un archivo aparte
        $cantidad_computadoras = Computadora::count();
        $cantidad_impresoras = Impresora::count();
        $cantidad_telefonos = Telefono::count();
        $pedidos = Pedido::where('status', 'pendiente')->with('user')->get();
        $reportes = Reporte::all();
        $usuarios = User::all();
        $respuestas_resguardos = Resguardo::with('user')->get();

        foreach($reportes as $reporte){
            $reporte->fecha_larga = Carbon::parse($reporte->fecha_reporte)->translatedFormat('l, d \d\e F \d\e Y');
        }



        return view('admin.perfil', compact('cantidad_computadoras', 'cantidad_impresoras', 'cantidad_telefonos', 'pedidos', 'reportes', 'usuarios', 'respuestas_resguardos'));
    }


    public function cerrar_session(){
        
        Session::flush(); //Manda alv la session
        Auth::logout();
        return back();

    }


    public function eliminar_usuario($id){

        $usuario = User::findOrFail($id);
        $usuario->delete();

        return back()->with('eliminado', 'El usuario fue eliminado');




    }


    public function actualizar_usuario($id){
    
        //encontrando el usuario
        $usuario = User::findOrFail($id);
        

        //comparando, si son diferentes se aplica el cambio si no no se hace nada
        if(request('password_edit') != $usuario->password){
            $usuario->password = bcrypt(request('password_edit'));
        }


        request()->validate([
            'nombre_edit' => 'required',
            // 'celular_edit' => 'required',
            // 'email_edit' => 'required|email|unique:users,email',
            'puesto_edit' => 'required',
            'planta_edit' => 'required',
            'extension_edit' => 'required',
        ]);


        $usuario->name = request('nombre_edit');
        $usuario->email = request('email_edit');
        $usuario->puesto = request('puesto_edit');
        $usuario->planta = request('planta_edit');
        $usuario->correo = request('correo_edit');
        $usuario->extension = request('extension_edit');
        $usuario->celular = request('celular_edit');
        $usuario->update();


        return back()->with('editado', 'El usuario fue editado!');



    }


    public function aceptar_resguardo($id){
        
        
        $usuario = User::findOrFail($id);
        $usuario->resguardo_firmado = true;
        $usuario->save();


        return back()->with('aceptado', 'Gracias por aceptar el resguardo!');
    
    }




    public function lista_dispositivos(){
    
        $computadoras = Computadora::all();
        $impresoras =   Impresora::all();
        $telefonos  = Telefono::all();


        
        return view('admin.lista_dispositivos', compact('computadoras', 'impresoras', 'telefonos'));
    }






    public function perfil_home(){
        $usuarios = User::orderBy('created_at', 'desc')->get();

        return view('user.perfil_directorio', compact('usuarios'));
    } 



    public function perfil_articles(){


        $publicaciones = Publicacion::where('categoria', 'articulos')->get();

        $tutoriales = Publicacion::where('categoria', 'tutoriales')->get();

        return view('user.articles', compact('publicaciones', 'tutoriales'));

    }


    public function configuracion(){
        $tintas = EmailSetting::obtener('tintas');
        $soporte = EmailSetting::obtener('soporte');
        return view('admin.configuracion', compact('tintas', 'soporte'));
    }

    public function config_tintas_update(){
        request()->validate([
            'mail_from' => 'required|email',
            'mail_from_name' => 'required',
            'destinatario' => 'required|email',
        ]);
        EmailSetting::updateOrCreate(
            ['seccion' => 'tintas'],
            [
                'mail_from' => request('mail_from'),
                'mail_from_name' => request('mail_from_name'),
                'destinatario' => request('destinatario'),
                'cc' => request('cc') ?: null,
            ]
        );
        return back()->with('config_guardada', 'Configuración de tintas guardada');
    }

    public function config_soporte_update(){
        request()->validate([
            'mail_from' => 'required|email',
            'mail_from_name' => 'required',
            'destinatario' => 'required|email',
        ]);
        EmailSetting::updateOrCreate(
            ['seccion' => 'soporte'],
            [
                'mail_from' => request('mail_from'),
                'mail_from_name' => request('mail_from_name'),
                'destinatario' => request('destinatario'),
                'cc' => request('cc') ?: null,
            ]
        );
        return back()->with('config_guardada', 'Configuración de soporte guardada');
    }

    public function admin_pedidos_tintas(){
        $pedidos = Pedido::with('user')->orderBy('created_at', 'desc')->get();
        return view('admin.pedidos_tintas', compact('pedidos'));
    }

    public function admin_soporte(){
        \Carbon\Carbon::setLocale('es');
        $reportes = Reporte::with('user')->orderBy('created_at', 'desc')->get();
        foreach($reportes as $reporte){
            $reporte->fecha_larga = \Carbon\Carbon::parse($reporte->fecha_reporte)->translatedFormat('l, d \\d\\e F \\d\\e Y');
        }
        return view('admin.soporte', compact('reportes'));
    }
}
