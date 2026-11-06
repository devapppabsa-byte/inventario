<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=.90">
    <link rel="shortcut icon" href="/img/pc.png" type="image/x-icon">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>

    {{-- CSS --}}
    <link rel="stylesheet" href="{{asset('css/app.css')}}">
    {{-- toastr antes de estilos.css para que el tema negro de .toast-negro
         piese a los colores propios de toastr sin usar !important. --}}
    <link rel="stylesheet" href="{{asset('vendor/toastr/toastr.min.css')}}">
    <link rel="stylesheet" href="{{asset('css/estilos.css')}}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />

    {{-- JS --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
</head>
<body>

@yield('contenido')

<script src="{{asset('js/app.js')}}"></script>
<script src="{{asset('/js/js.js')}}"></script>
{{-- Reduce las imagenes en el navegador antes de subirlas: Vercel corta con 413
     cualquier cuerpo mayor a 4.5 MB y ese limite no se puede tocar con php.ini.
     Va en el layout para que lo usen tickets y tintas por igual. --}}
<script src="{{asset('js/compresor-imagenes.js')}}"></script>
{{-- Loader a pantalla completa para los modales de tickets y de tintas. Tapa
     header, body y footer, y trae un watchdog para que un envio colgado no
     deje al usuario encerrado en el modal. --}}
<script src="{{asset('js/loader-modal.js')}}"></script>

{{-- Notificaciones de session con toastr. El mapa de claves y su tipo esta en
     config/notificaciones.php; vaciar ese mapa desactiva los toasts sin tocar
     los bloques en linea que ya existen en cada vista. --}}
@php($notificacionesToast = \App\NotificacionesSession::paraElToast())
@if ($notificacionesToast)
<script type="application/json" id="notificaciones-session">@json($notificacionesToast)</script>
<script src="{{asset('vendor/toastr/toastr.min.js')}}"></script>
<script src="{{asset('js/toastr-notificaciones.js')}}"></script>
@endif

<script>
  document.addEventListener('DOMContentLoaded', function() {
  });
</script>

<script>
  if(document.getElementById('form-cerrar-session')){
    const cerrar_session = document.getElementById('cerrar_session');
    const form_cerrar_session = document.getElementById('form-cerrar-session');

    cerrar_session.addEventListener('click', function(){
      cerrar_session.disabled = true;
      form_cerrar_session.submit();
      setTimeout(() => {
        cerrar_session.disabled = false;
      }, 3000);
    })
  }
</script>

@yield('scripts')

</body>
</html>
