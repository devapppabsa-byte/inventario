<?php
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\pcController;
use App\Http\Controllers\upsController;
use App\Http\Controllers\areaController;
use App\Http\Controllers\homeController;
use App\Http\Controllers\tintasController;
use App\Http\Controllers\accesosController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\printerController;
use App\Http\Controllers\ticketsController;
use App\Http\Controllers\postUserController;
use App\Http\Controllers\telefonoController;
use App\Http\Controllers\resguardoController;
use App\Http\Controllers\directorioController;
use App\Http\Controllers\reaccionesController;
use App\Http\Controllers\comentariosController;
use App\Http\Controllers\publicacionesController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\AgenteSoporteAdminController;
use App\Http\Controllers\adjuntosController;
use App\Http\Controllers\EvaluacionesController;
use App\Http\Controllers\EvaluacionesAdminController;


//Rutas para ir a los dispositivos
Route::get('/admin/lista_dispositivos/lista_computadoras/', [pcController::class, 'lista_computadoras'])->name('lista.computadoras');
Route::get('/admin/lista_dispositivos/lista_computadoras/{id}', [pcController::class, 'editar_computadora_show'])->name('editar.computadora');
Route::patch('/admin/listas_dispositivos/lista_computadoras/actualizar/{id}', [pcController::class, 'editar_computadora_update'] )->name('actualizar.computadora');


Route::get('/admin/lista_dispositivos/lista_impresoras/', [printerController::class, 'lista_impresoras'])->name('lista.impresoras')->middleware('auth:admin');
Route::get('/admin/lista_dispositivos/lista_impresoras/editar/{id}', [printerController::class, 'editar_impresora_show'])->name('editar.impresora')->middleware('auth:admin');
Route::patch('/admin/lista_dispositivos/lista_impresoras/actualizar/{id}', [printerController::class, 'editar_impresora_update'])->name('impresora.actualizar')->middleware('auth:admin');


Route::get('/admin/lista_dispositivos/lista_telefonos/', [telefonoController::class, 'lista_telefonos'])->name('lista.telefonos')->middleware('auth:admin');
Route::get('/admin/lista_dispositivos/lista_telefonos/editar/{id}', [telefonoController::class, 'editar_telefono_show'])->name('editar.telefono')->middleware('auth:admin');
Route::patch('/admin/lista_dispositivos/lista_telefonos/editar/actualizado/{id}', [telefonoController::class, 'editar_telefono_update'])->name('telefono.update')->middleware('auth:admin');





//Rutas para editar dispositivos


// Route::get('/admin/lista_dispositivos/lista_computadoras/{id}/editar', [pcController::class, 'editar_computadora_show'])->name('editar.computadora');








Route::get('/admin/lista_dispositivos', [Controller::class, 'lista_dispositivos'] )->name('lista.dispositivos');


//RESPUESTA A LOS PEDIDOS DE TINTAS

Route::patch('/admin/respuesta/{id}', [tintasController::class, 'respuesta_admin'])->name('respuesta.admin')->middleware('auth:admin');

Route::get('/admin/reporte/{id}', [ticketsController::class, 'detalle_reporte_admin'])->name('detalle.reporte.admin')->middleware('auth:admin');
Route::post('/admin/reporte/comentario/{id}', [ticketsController::class, 'comentario_admin'])->name('comentario.reporte.admin')->middleware('auth:admin');


// Agentes de soporte: guard aparte, asi que no alcanzan ninguna ruta auth:admin.
Route::middleware('auth:soporte')->group(function () {
    Route::get('/soporte', [Controller::class, 'soporte_index'])->name('soporte.index');
    Route::get('/soporte/reporte/exportar', [Controller::class, 'soporte_exportar'])->name('soporte.exportar');
    Route::get('/soporte/reporte/{id}', [ticketsController::class, 'soporte_detalle_reporte'])->name('soporte.detalle');
    Route::post('/soporte/reporte/comentario/{id}', [ticketsController::class, 'soporte_comentario'])->name('soporte.comentario');
    Route::post('/soporte/cerrar_sesion', [Controller::class, 'soporte_cerrar_sesion'])->name('soporte.cerrar_sesion');
});



// Route::get('/', [homeController::class, 'index'])->name('login');
Route::get('/', [homeController::class, 'index'])->name('login');
Route::post('/', [Controller::class, 'ingreso_admin'])->name('ingreso.admin');
Route::get('/admin', [Controller::class, 'perfil_admin'])->name('perfil.admin')->middleware('auth:admin');
Route::post('/admin', [Controller::class, 'cerrar_session'])->name('cerrar.session.admin');

// Configuración de correo
Route::get('/admin/configuracion', [Controller::class, 'configuracion'])->name('admin.configuracion')->middleware('auth:admin');
Route::patch('/admin/configuracion/tintas', [Controller::class, 'config_tintas_update'])->name('admin.config.tintas.update')->middleware('auth:admin');
Route::patch('/admin/configuracion/soporte', [Controller::class, 'config_soporte_update'])->name('admin.config.soporte.update')->middleware('auth:admin');

// Pedidos tintas sección admin
Route::get('/admin/pedidos-tintas', [Controller::class, 'admin_pedidos_tintas'])->name('admin.pedidos.tintas')->middleware('auth:admin');
Route::patch('/admin/pedidos-tintas/{id}/completar', [Controller::class, 'admin_pedido_completar'])->name('admin.pedidos.completar')->middleware('auth:admin');

// Solicitudes de soporte sección admin
Route::get('/admin/soporte', [Controller::class, 'admin_soporte'])->name('admin.soporte')->middleware('auth:admin');
Route::get('/admin/soporte/exportar', [Controller::class, 'admin_soporte_exportar'])->name('admin.soporte.exportar')->middleware('auth:admin');

//  Ingreso de usuarios

Route::patch('/admin/agregando_users/{id}/eliminado', [Controller::class, 'eliminar_usuario'])->name('usuario.eliminar')->middleware('auth:admin');
Route::patch('/admin/agregando_users/{id}/editado',[Controller::class, 'actualizar_usuario'] )->name('actualizar.usuario')->middleware('auth:admin');
Route::get('/admin/agregando_users/resguardo/{id}', [resguardoController::class, 'show_admin'])->name('view.resguardo.admin')->middleware('auth:admin');
Route::get('/admin/agregando_users/accesos/{id}', [accesosController::class, 'show_admin'] )->name('view.accesos.admin')->middleware('auth:admin');
Route::patch('/admin/agregando_users/accesos/autoriza_software/{id}', [accesosController::class, 'autoriza_software'])->middleware('auth:admin')->name('autorizar.software');
Route::patch('/admin/agregando_users/accesos/desautoriza_software/{id}', [accesosController::class, 'desautoriza_software'])->name('desautorizar.software');
Route::patch('/admin/agregando_users/accesos/autorizar_sitio/{id}', [accesosController::class, 'autoriza_sitio'])->name('autorizar.sitio');
Route::patch('/admin/agregando_users/accesos/desautorizar_sitio/{id}', [accesosController::class, 'desautoriza_sitio'])->name('desautorizar.sitio');

//rutas para agregar a los usuarios
Route::get('/admin/agregando_users', [Controller::class, 'show_formulario'])->name('agregar.usuarios')->middleware('auth:admin');


Route::post('/agregando_users', [Controller::class, 'registrar_usuarios'])->name('registrar.usuarios')->middleware('auth:admin');
Route::post('/agregando/usuarios/excel', [Controller::class, 'importar_usuarios_excel'])->name('usuarios.excel')->middleware('auth:admin');
Route::get('/agregando/usuarios/plantilla', [Controller::class, 'descargar_plantilla_usuarios'])->name('usuarios.plantilla')->middleware('auth:admin');



//rutas que se encargan de mostrar los resultados de la busqueda




//rutas que se encargan de la gestions de las computadoras
Route::get('admin/add_pc', [pcController::class, 'store'])->name('add_pc')->middleware('auth:admin');
Route::post('admin/add_pc', [pcController::class, 'create'])->name('pc.create')->middleware('auth:admin');

//rutas que se encargan de mostar los detalles de las computadoras 
Route::get('/resultado_pc', [pcController::class, 'show'])->name('pc.show')->middleware('auth:admin');




//rutas que se encargan de los UPS
Route::get('admin/add_telefono', [telefonoController::class, 'store'])->name('add.telefono')->middleware('auth:admin');
Route::post('admin/add_telefono', [telefonoController::class, 'create'])->name('telefono.create')->middleware('auth:admin');

//ruta que va a los detalles del regulador
Route::get('/resultado_ups', [telefonoController::class, 'show'])->name('ups.show')->middleware('auth:admin');





//rutas que se encargan de las impresoras
Route::get('admin/add_printer', [printerController::class, 'store'])->name('add_printer')->middleware('auth:admin');;
Route::post('admin/add_printer', [printerController::class, 'create'])->name('printer.create')->middleware('auth:admin');;

//las credenciales para que entre alguien a autorizar las tintas
Route::get('admin/autoriza_tintas', [tintasController::class, 'autorizar_tintas'])->name('perfil.autoriza_tintas')->middleware('auth:admin');


//rutas del panekl de control de las publicaciones
Route::get('admin/publicaciones', [publicacionesController::class, 'show'])->name('gestionar.publicaciones')->middleware('auth:admin');
Route::get('admin/publicaciones/nueva', [publicacionesController::class, 'agregar_post'])->name('agregar.post')->middleware('auth:admin');
Route::post('admin/publicaciones/nueva/post', [publicacionesController::class, 'post_store'])->name('post.store')->middleware('auth:admin');











// Route::get('/dispositivo{serie}', [homeController::class, 'show'])->name('device.show');

Route::get('/user/tickets/detalles/{id}', [ticketsController::class, 'detalle_reporte'])->name('detalle.reporte')->middleware('auth');

Route::get('/user/tickets/imagen/{id}', [ticketsController::class, 'mostrar_imagen'])->name('reporte.imagen')->middleware('auth');

// Evidencia de los reportes. Sin middleware a proposito: son tres guards distintos
// (usuario, admin y agente) y el permiso se resuelve dentro del controlador.
Route::get('/adjuntos/reporte/{id}', [adjuntosController::class, 'show'])->name('adjuntos.show');

//Rutas que funcionan en el perfil de los usuarios
Route::get('/user/home', [Controller::class, 'perfil_home'])->name('perfil.home')->middleware('auth');
Route::get('/user/articles', [Controller::class, 'perfil_articles'])->name('perfil.articles')->middleware('auth');
Route::get('/user/web-tools', [Controller::class, 'perfil_user'])->name('perfil.user')->middleware('auth');
Route::get('/user/dispositivos', [Controller::class, 'dispositivos_show'])->name('dispositivos.show')->middleware('auth');
Route::get('/user/tintas', [tintasController::class, 'show'])->name('tintas.show')->middleware('auth');
Route::get('/user/tickets', [ticketsController::class, 'show'])->name('tickets.show')->middleware('auth');
Route::post('/user/tintas/pedido', [tintasController::class, 'pedido'])->name('tintas.pedido')->middleware('auth');
Route::post('/user/tintas/pedido/{id}/reenvio', [tintasController::class, 'reenviar_pedido'])->name('tintas.reenvio')->middleware('auth');
Route::patch('/user/tintas/pedido/{id}', [tintasController::class, 'pedido_completo'])->name('pedido.completo')->middleware('auth');



Route::post('/user/tickets/', [ticketsController::class, 'reporte'])->name('reporte.post');
Route::post('/user/tickets/{id}/reenvio', [ticketsController::class, 'reenviar_reporte'])->name('reporte.reenvio')->middleware('auth');
Route::patch('/user/tickets/{id}/completo', [ticketsController::class, 'reporte_completo'])->name('reporte.completo');
Route::post('/user/tickets/comentario/{id}', [ticketsController::class, 'comentario_usuario'])->name('comentario.reporte.usuario');

//directorio
Route::get('/user/directorio', [directorioController::class, 'show'])->name('directorio.show')->middleware('auth');

Route::post('/user/cerrar_sesion', [Controller::class, 'cerrar_session'])->name('cerrar.session');
Route::get('/user/resguardo', [resguardoController::class, 'show'])->name('user.resguardo')->middleware('auth');
Route::post('/user/resguardo/confirma/{id}', [Controller::class, 'aceptar_resguardo'])->name('confirma.resguardo');
Route::post('/user/resguardo/observaciones', [resguardoController::class, 'observaciones'])->name('observaciones.resguardo');


Route::get('/user/control_accesos', [accesosController::class, 'show'])->name('user.accesos')->middleware('auth');
Route::post('/user/control_accesos/solicitar_sitio', [accesosController::class, 'solicita_sitio'])->name('peticion.sitio');
Route::post('/user/control_accesos/solicitar_software', [accesosController::class, 'solicita_software'])->name('peticion.software');




///Creando las rutas que van a ser de un jefe
Route::get('/user/permisos', [accesosController::class, 'ver_permisos_jefe'])->name('permisos.show')->middleware('auth');
Route::patch('/user/permisos/autoriza_software/{id}', [accesosController::class, 'autoriza_software_jefe'])->name('autorizar.software.jefe')->middleware('auth');
Route::patch('/user/permisos/desautoriza_software/{id}', [accesosController::class, 'desautoriza_software_jefe'])->name('desautorizar.software.jefe')->middleware('auth');
Route::patch('/user/permisos/autorizar_sitio/{id}', [accesosController::class, 'autoriza_sitio_jefe'])->name('autorizar.sitio.jefe')->middleware('auth');
Route::patch('/user/permisos/desautorizar_sitio/{id}', [accesosController::class, 'desautoriza_sitio_jefe'])->name('desautorizar.sitio.jefe')->middleware('auth');



//Rutas para elñiminar los accesos solicitados por sui se equivoca o se cansa de pedirlo

Route::delete('/user/permisos/eliminar/{id}', [accesosController::class, 'eliminar_acceso'])->name('eliminar.acceso')->middleware('auth');



//Rutas que hacen que el usuario cargue las imagenes de los dispositivos

Route::patch('/user/resguardo/imagenes_pc/{id}', [pcController::class, 'fotos_computadora_usuario'])->name('fotos.computadora.usuario');
Route::patch('/user/resguardo/imagenes_printer/{id}', [printerController::class, 'fotos_impresora_usuario'])->name('fotos.impresora.usuario');
Route::patch('/user/resguardo/imagenes_phone/{id}', [telefonoController::class, 'fotos_telefono_usuario'])->name('fotos.telefono.usuario');



//Rutas que se encargan de mostrar los post hechois por el area de sistemas
Route::get('/user/home/post/{id}', [publicacionesController::class, 'mostrar_post'])->name('mostrar.post')->middleware('auth');
Route::post('/user/home/post/reaccion', [reaccionesController::class, 'reaccion_store'])->name('reaccion.store')->middleware('auth');
Route::post('/user/home/post/comentario', [comentariosController::class, 'store'])->name('comentario.store')->middleware('auth');



//rutas de los post en el muro


Route::post('/user/home', [postUserController::class, 'postUser'])->name('post.user.store');


//rutas de los post en el muro



// ============================================================
// EVALUACIONES DE PROVEEDORES
// ============================================================

// ---- Panel Admin: Agentes de Soporte ----
Route::get('/admin/soporte_agentes', [AgenteSoporteAdminController::class, 'index'])->name('admin.soporte.agentes')->middleware('auth:admin');
Route::post('/admin/soporte_agentes', [AgenteSoporteAdminController::class, 'store'])->name('admin.soporte.agentes.store')->middleware('auth:admin');
Route::patch('/admin/soporte_agentes/{id}', [AgenteSoporteAdminController::class, 'update'])->name('admin.soporte.agentes.update')->middleware('auth:admin');
Route::patch('/admin/soporte_agentes/{id}/toggle', [AgenteSoporteAdminController::class, 'toggle'])->name('admin.soporte.agentes.toggle')->middleware('auth:admin');
Route::delete('/admin/soporte_agentes/{id}', [AgenteSoporteAdminController::class, 'destroy'])->name('admin.soporte.agentes.destroy')->middleware('auth:admin');

// ---- Panel Admin: Proveedores ----
Route::get('/admin/proveedores', [ProveedorController::class, 'index'])->name('admin.proveedores')->middleware('auth:admin');
Route::post('/admin/proveedores', [ProveedorController::class, 'store'])->name('admin.proveedores.store')->middleware('auth:admin');
Route::patch('/admin/proveedores/{id}', [ProveedorController::class, 'update'])->name('admin.proveedores.update')->middleware('auth:admin');
Route::patch('/admin/proveedores/{id}/toggle', [ProveedorController::class, 'toggle'])->name('admin.proveedores.toggle')->middleware('auth:admin');
Route::delete('/admin/proveedores/{id}', [ProveedorController::class, 'destroy'])->name('admin.proveedores.destroy')->middleware('auth:admin');

// ---- Panel Admin: Evaluaciones ----
Route::get('/admin/evaluaciones', [EvaluacionesAdminController::class, 'index'])->name('admin.evaluaciones')->middleware('auth:admin');
Route::get('/admin/evaluaciones/crear', [EvaluacionesAdminController::class, 'create'])->name('admin.evaluaciones.create')->middleware('auth:admin');
Route::post('/admin/evaluaciones', [EvaluacionesAdminController::class, 'store'])->name('admin.evaluaciones.store')->middleware('auth:admin');

Route::get('/admin/evaluaciones/{id}', [EvaluacionesAdminController::class, 'show'])->name('admin.evaluaciones.show')->middleware('auth:admin');

Route::patch('/admin/evaluaciones/{id}', [EvaluacionesAdminController::class, 'update'])->name('admin.evaluaciones.update')->middleware('auth:admin');
Route::patch('/admin/evaluaciones/{id}/toggle', [EvaluacionesAdminController::class, 'toggle'])->name('admin.evaluaciones.toggle')->middleware('auth:admin');
Route::delete('/admin/evaluaciones/{id}', [EvaluacionesAdminController::class, 'destroy'])->name('admin.evaluaciones.destroy')->middleware('auth:admin');

// Secciones
Route::post('/admin/evaluaciones/{id}/secciones', [EvaluacionesAdminController::class, 'seccionStore'])->name('admin.secciones.store')->middleware('auth:admin');
Route::patch('/admin/secciones/{id}', [EvaluacionesAdminController::class, 'seccionUpdate'])->name('admin.secciones.update')->middleware('auth:admin');
Route::delete('/admin/secciones/{id}', [EvaluacionesAdminController::class, 'seccionDestroy'])->name('admin.secciones.destroy')->middleware('auth:admin');

// Preguntas
Route::post('/admin/secciones/{id}/preguntas', [EvaluacionesAdminController::class, 'preguntaStore'])->name('admin.preguntas.store')->middleware('auth:admin');
Route::patch('/admin/preguntas/{id}', [EvaluacionesAdminController::class, 'preguntaUpdate'])->name('admin.preguntas.update')->middleware('auth:admin');
Route::delete('/admin/preguntas/{id}', [EvaluacionesAdminController::class, 'preguntaDestroy'])->name('admin.preguntas.destroy')->middleware('auth:admin');

// Opciones
Route::post('/admin/preguntas/{id}/opciones', [EvaluacionesAdminController::class, 'opcionStore'])->name('admin.opciones.store')->middleware('auth:admin');
Route::patch('/admin/opciones/{id}', [EvaluacionesAdminController::class, 'opcionUpdate'])->name('admin.opciones.update')->middleware('auth:admin');
Route::delete('/admin/opciones/{id}', [EvaluacionesAdminController::class, 'opcionDestroy'])->name('admin.opciones.destroy')->middleware('auth:admin');

// Proveedores asociados a evaluación
Route::post('/admin/evaluaciones/{id}/proveedores', [EvaluacionesAdminController::class, 'proveedorAssociate'])->name('admin.evaluaciones.proveedores.associate')->middleware('auth:admin');
Route::delete('/admin/evaluaciones/{evaluacionId}/proveedores/{proveedorId}', [EvaluacionesAdminController::class, 'proveedorDissociate'])->name('admin.evaluaciones.proveedores.dissociate')->middleware('auth:admin');

// ---- Panel Admin: Resultados de evaluaciones ----
Route::get('/admin/resultados/proveedores', [EvaluacionesAdminController::class, 'resultadosProveedores'])->name('admin.resultados.proveedores')->middleware('auth:admin');
Route::get('/admin/resultados/intento/{intento}', [EvaluacionesAdminController::class, 'resultadoIntento'])->name('admin.resultados.intento')->middleware('auth:admin');

// ---- Panel Usuario: Evaluaciones ----
Route::get('/user/evaluaciones', [EvaluacionesController::class, 'misEvaluaciones'])->name('evaluaciones.mis')->middleware('auth');
Route::get('/user/evaluaciones/{evaluacionId}/responder', [EvaluacionesController::class, 'responder'])->name('evaluaciones.responder')->middleware('auth');
Route::get('/user/evaluaciones/{evaluacionId}/subencuesta/{seccionId}/responder', [EvaluacionesController::class, 'responderSeccion'])->name('evaluaciones.responderSeccion')->middleware('auth');
Route::post('/user/evaluaciones/{evaluacionId}/iniciar', [EvaluacionesController::class, 'iniciar'])->name('evaluaciones.iniciar')->middleware('auth');
Route::get('/user/evaluaciones/intento/{intentoId}', [EvaluacionesController::class, 'intento'])->name('evaluaciones.intento')->middleware('auth');
Route::post('/user/evaluaciones/intento/{intentoId}/guardar', [EvaluacionesController::class, 'guardar'])->name('evaluaciones.guardar')->middleware('auth');
Route::get('/user/evaluaciones/intento/{intentoId}/resultado', [EvaluacionesController::class, 'resultado'])->name('evaluaciones.resultado')->middleware('auth');
