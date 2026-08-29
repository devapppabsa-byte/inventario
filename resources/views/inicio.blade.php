@extends('layout')
@section('contenido')

<div class="container mt-5">
    <div class="row mt-5 justify-content-center">
        <div class="col-sm-8 col-md-6 col-lg-3 mt-5 border border-3 bg-white p-4">
         <form action="{{route('ingreso.admin')}}" method="POST" id="login">
            @csrf
            <h3>Login</h3>
            @if (session('error'))
                <span class="text-danger fw-bold">{{session('error')}}</span>
            @endif
            
            <div class="mb-3 m-0">
                <label for="email" class="m-0 p-0">Usuario</label>
                <input type="text"   name="email" class="form-control" id="email" >

            </div>

            <div class="mb-3 m-0 mt-3">
                <label for="password" class="m-0 p-0">Password</label>
                <input type="password" name="password" id="password" class="form-control">
            </div>

            <div class="mb-3 m-0 mt-3">
                <label for="" class="m-0 p-0">Tipo de usuario:</label>
                <select class="form-control" name="tipo" id="">
                    <option value="usuario" >Usuario</option>
                    <option value="administrador" >Administrador</option>
                    {{-- <option value="autoriza" >Autorizar Tintas</option> --}}
                </select>
            </div>

            <div class="mb-3 m-0 mt-3">
                <button type="submit" class="btn btn-dark" id="boton">
                    Entrar
                </button>
            </div>
        </form>
        </div>
    </div>
</div>



<script>
   document.getElementById('login').addEventListener('submit', function(){
    let boton = document.getElementById('boton');
    boton.disabled = true;
    boton.style.cursor = "not-allowed";
    boton.style.opacity = "0.6";
})
</script>

@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    gsap.from('.col-sm-8', { opacity: 0, y: 50, scale: 0.95, duration: 0.7, ease: 'power3.out' });
    gsap.from('h3', { opacity: 0, x: -20, duration: 0.5, delay: 0.3, ease: 'power2.out' });
    gsap.from('.mb-3', { opacity: 0, y: 15, duration: 0.4, stagger: 0.1, delay: 0.4, ease: 'power2.out' });
  });
</script>
@endsection