@extends('layout')
@section('title', 'Home')
@section('contenido')
@include('user.cabecera')

<div class="container-fluid fade-out" id="content">

    <div class="row justify-content-around mt-5 bg-light py-5">
      <div class="col-8 ">
            <div class="row">
                <div class="col-12">
                    <h2 class="font-arimo text-center">Articulos de Seguridad Informatica</h2>
                </div>

            @forelse ($publicaciones as $publicacion)

            <div class="col-6 zoom_seguridad_info">

                    <a href="{{route('mostrar.post', $publicacion->id)}}" class="text-decoration-none">
                        <div class="row d-flex align-items-center justify-content-center m-4  bg-white shadow p-4">
                        <div class="col-12 text-center p-2">
                            <h3 class="font-maker">{{$publicacion->titulo}}</h3>
                        </div>
                        <div class="col-12 p-4">
                            <img src="{{Storage::url($publicacion->portada)}}" class="img-fluid w-100" alt="">
                        </div>
                        
                        
                        <div class="col-12 text-center" >
                            <p style="text-align: justify" class="text_cut font-arimo h5">
                                {{$publicacion->introduccion}}
                            </p>
                        </div>                            
                    </div>
                </a>
            </div>
                        

            @empty
            <div class="row">
                <div class="col-12 text-center p-5">
                    <h3 class="m-4">Sin Articulos</h3>
                    <i class="fa fa-magic fa-5x"></i>
                </div>
            </div>
            @endforelse
            </div>
    </div>




    <div class="col-4">
            <h2 class="font-arimo text-center">Tutoriales</h2>

            @forelse ($tutoriales as $tutorial)
                <a href="{{route('mostrar.post', $tutorial->id)}}" class="text-decoration-none text-dark shadow ">
                    <div class="row m-4 bg-white p-2 d-flex align-items-center zoom_seguridad_info shadow-sm">
                        <div class="col-12 text-center">
                            <h3 class="cascadia" >{{$tutorial->titulo}}</h3>
                        </div>
                        <div class="col-3">
                            <img src="{{Storage::url($tutorial->portada)}}" class="img-fluid" alt="">
                        </div>
                        <div class="col-9">
                            <p style="text-align: justify" class="text_cut font-arimo h5">
                                {{$tutorial->introduccion}}
                            </p>
                        </div>
                    </div>
                </a>                
            @empty

            <div class="row">
                <div class="col-12 text-center p-5">
                    <h3 class="m-4">Sin Tutoriales</h3>
                    <i class="fa fa-magic fa-5x"></i>
                </div>
            </div>
                
            @endforelse




    </div>

    </div>



</div>


@endsection    


{{-- esta es l seccion de los scripts --}}
@section('scripts')
<script>


    $(document).ready(function(){
        $('.reaccion_boton').click(function(e){
           

            e.preventDefault();

            let reaccion = $(this).data('reaccion');
            let form = $("#reaccion_formulario");
            let formData = form.serialize() + "&reaccion" + reaccion;

            $.ajax({
                url: form.attr("action"),
                type: "POST",
                data:formData,

                success: function(response){
                    $("#loveit_count").text(response.loveit_count);
                    $("#like_count").text(response.like_count);
                    $("#dislike_count").text(response.dislike_count);
                },
                error: function(response){
                    alert("Hubo un error!" + response);
                    console.log(response)
                }

            })
        })
    })
</script>




<script>


  const maxPalabras = 15;
  const divs = document.querySelectorAll('.text_cut');

  divs.forEach(div => {
    const palabras = div.innerText.trim().split(/\s+/);
    if (palabras.length > maxPalabras) {
      const recorte = palabras.slice(0, maxPalabras).join(" ") + '...';
      div.innerText = recorte;
    }
  });


</script>
@endsection