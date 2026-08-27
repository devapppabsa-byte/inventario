@php
    use Carbon\Carbon;
@endphp
@extends('layout')
@section('title', 'Publicaciones')
@section('contenido')
@include('user.cabecera')



{{-- <button class="btn btn-dark" data-bs-toggle="tooltip" data-placement="top"
title="Leer Ariculo: {{$publicacion->titulo}}" onclick="leerTexto()">
    <i class="fa fa-pause"></i>
</button> --}}


<div class="container  bg-white shadow shadow-sm" id="content">
    <div class="row justify-content-center">
        <div class="col-6 text-center  my-3 mt-5">
            <h1 class="titulo-post" >{{$publicacion->titulo}}</h1>
            <span class="text-sub">Recuperado de internet por: <u> {{$publicacion->autor}} <i class="fa fa-check-circle mx-1"></i> </u> </span>

        </div>
        <div class="col-12 text-center my-3 p-0 mt-1">
            <form action="{{ route('reaccion.store') }}" method="POST">
                @csrf
                <input type="hidden" name="user_id" value="{{ Auth::user()->id }}">
                <input type="hidden" name="publicacion_id" value="{{ $publicacion->id }}">
                <h5>Reacciones: </h5>
                <div class="btn-group">
                    <button type="submit" 
                        class="btn btn-danger d-block btn-sm" 
                        name="reaccion" 
                        value="loveit"
                        data-bs-toggle="tooltip" 
                        data-placement="top"
                        title="@foreach($publicacion->reacciones->where('reaccion', 'loveit') as $reaccion) {!! $reaccion->user->name . ' - ' !!} @endforeach">
                        <i class="fa fa-heart text-white"></i>
                        {{$contador_loveit}}
                    </button>
            
                    <button type="submit" 
                        class="btn btn-primary d-block btn-sm" 
                        name="reaccion" 
                        value="like"
                        data-bs-toggle="tooltip" 
                        data-placement="top"
                        title="@foreach($publicacion->reacciones->where('reaccion', 'like') as $reaccion) {!! $reaccion->user->name . ' - ' !!} @endforeach">
                        <i class="fa fa-thumbs-up"></i>
                        {{$contador_likes}}
                    </button>
            
                    <button type="submit" 
                        class="btn btn-secondary d-block btn-sm" 
                        name="reaccion" 
                        value="dislike"
                        data-bs-toggle="tooltip" 
                        data-placement="top"
                        title="@foreach($publicacion->reacciones->where('reaccion', 'dislike') as $reaccion) {!! $reaccion->user->name . ' - ' !!} @endforeach">
                        <i class="fa-solid fa-thumbs-down"></i>
                        {{$contador_dislikes}}
                    </button>
                </div>
            </form>
            
            
        </div>
        
        {{-- <div class="col-12 text-center mt-2">
            
            @php $reaccionUsuario = optional($reaccion->first())->reaccion; @endphp

            @if ($reaccionUsuario)
                <span class="fw-bold h3 bg-light p-3 border border-4">
                    {!! $reaccionUsuario == 'like' ? '<i class="fa fa-thumbs-up text-primary text-underline"></i> You Like This' : '' !!}
                    {!! $reaccionUsuario == 'dislike' ? '<i class="fa fa-thumbs-down text-secondary text-underline"></i> You Dislike This' : '' !!}
                    {!! $reaccionUsuario == 'loveit' ? '<i class="fa fa-heart text-danger text-underline"></i> You Love This' : '' !!}
                </span>
            @endif

        </div> --}}
        
        
        <div class="col-10 mt-5 text-justify">
            <b>Introducción: </b>
            <p>{{$publicacion->introduccion}}</p> 
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-8 text-center m-3">
            <img src="{{asset(Storage::url($publicacion->portada))}}" class="img-fluid w-75 m-5" alt="">
        </div>
    </div>


    <div class="row justify-content-center">
        <div class="col-12 text-center my-3">
            <div class="btn-group">
                {{-- <button class="btn btn-success" onclick="leerTexto()">
                    <i class="fa fa-pause"></i>
                </button>
                <button class="btn btn-warning" onclick="leerTexto()">
                    <i class="fa fa-play"></i>
                    <i class="fa fa-pause"></i>

                </button> --}}
            </div>
        </div>

            <div class="col-8 text-justify" id="texto">
                @if ($publicacion->id == 14)

                    <iframe width="100%" height="500" src="https://www.youtube.com/embed/oTu2GQP5-u8" title="Table with Graph Dynamic - No Talking" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe> 

                @else



                    {!!$publicacion->cuerpo!!}


                
                @endif
            </div>


        
            @if ($publicacion->link_youtube)
                <div class="col-8 text-justify">
                    {!! $publicacion->link_youtube !!}
                </div>
            @else

        @endif


        
    </div>
    <br>
    <br>
    <br>
    <br>
    <br>
</div>

<div class="container mt-4 bg-white shadow p-4">

    <div class="row ">
        <div class="col-12">
            <h4 class="titulos">Comentarios</h4>
        </div>
    </div>  


    <div class="row mt-3" id="comentariosContenedor">
        @forelse ($publicacion->comentarios as $comentario)
            <div class="col-12 ">
                <div class="mb-3">
                    <b class="text-secondary">@php $nombre = DB::table('users')->where('id',  $comentario->user_id )->get(); echo $nombre[0]->name;@endphp</b> <br>

                    <p class="bg-gray px-3">
                        {{$comentario->comentario}}
                    </p>

                    <small class="p-0">{{$comentario->created_at->diffForHumans()}}</small>
                </div>
                <hr style="width: 95%; height: 2px; background-color: gray; border: none; margin: 5px auto;">
            </div>
        @empty

        @endforelse
    </div>


    <div class="row ">
        <div class="col-12 p-4">
            <div class="mb-3">
                <b>{{Auth()->user()->name}}</b>
                <form action="{{route('comentario.store')}}" method="POST" id="comentario" >
                    @csrf
                    <input type="hidden" name="id_user" value="{{Auth::user()->id}}">
                    <input type="hidden" name="id_publicacion" value="{{$publicacion->id}}">
                    <textarea name="comentario" class="w-100 form-control my-2"  placeholder="Comentario ... " ></textarea>
                    <button class="btn btn-success" type="submit">Comentar</button>
                </form>
            </div>
        </div>
    </div>
</div>


{{-- el boton flotante que le da play a la lectura del texto --}}
{{-- <button class="btn flotante" data-bs-toggle="tooltip" data-placement="left"
title="Leer Ariculo: {{$publicacion->titulo}}" onclick="leerTexto()">
    <i class="fa fa-play"></i>
</button> --}}

<button id="play" class="flotante" data-bs-toggle="tooltip" data-placement="top" title="Leer:  {{$publicacion->titulo}}"  onclick="leerTexto()">
    <i class="fa fa-play"></i>
</button>
<button id="pause" class="flotante"  onclick="pausarTexto()">
    <i class="fa fa-pause"></i>
</button>



@endsection

{{-- <script>
    const synth = window.speechSynthesis
    let texto = "Hola mundo desde api de js"
    const utterThis = new SpeechSynthesisUtterance(texto)

    synth.speak(utterThis)

</script> --}}



<script>


    window.speechSynthesis.cancel()

    let speech = new SpeechSynthesisUtterance();
            let synth = window.speechSynthesis;
            let palabras = [];
            let indicePalabra = 0;
            let enPausa = false;
    
            function leerTexto() {
                synth.cancel();
                let div = document.getElementById("texto");
                palabras = div.innerText.split(" "); // Dividir texto en palabras
                indicePalabra = 0;
    
                speech.text = div.innerText;
                speech.lang = "es-ES";
                speech.rate = 1;
                speech.pitch = 1;
                speech.volume = 1;
    
                speech.onboundary = (event) => {
                    if (event.name === "word") {
                        resaltarPalabra(indicePalabra);
                        indicePalabra++;
                    }
                };
    
                // Ocultar Play y mostrar Pause
                document.getElementById("play").style.display = "none";
                document.getElementById("pause").style.display = "inline-block";
    
                synth.speak(speech);
                enPausa = false;
            }
    
            function pausarTexto() {
                if (!enPausa) {
                    synth.pause();
                    document.getElementById("pause").innerText = "▶";
                    enPausa = true;
                } else {
                    synth.resume();
                    document.getElementById("pause").innerText = "⏸";
                    enPausa = false;
                }
            }
    
    
    
        function resaltarPalabra(index) {
            let div = document.getElementById("texto");
            let nuevoTexto = palabras.map((palabra, i) => {
                return i === index ? `<span style="background-color: black; color:white;">${palabra}</span>` : palabra;
            }).join(" ");
            div.innerHTML = nuevoTexto;
        }
    
        window.addEventListener("beforeunload", () => {
            synth.cancel();
        })
    
    
    </script>



</script>




<script>
    
document.addEventListener("DOMContentLoaded", function () { 
    // Esperamos a que la página cargue completamente antes de ejecutar el código

    document.getElementById("comentario").addEventListener("submit", function (e) {
        e.preventDefault(); // Evita que la página se recargue al enviar el formulario
        let form = this; // Referencia al formulario
        let formData = new FormData(form); // Creamos un objeto FormData con los datos del formulario

        // Enviamos la solicitud con Fetch API
        fetch("{{ route('comentario.store') }}", {
            
            method: "POST", // Método HTTP
            headers: {
                "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value // Token CSRF para seguridad
            },
            body: formData // Enviamos los datos del formulario
        })

        .then(response => response.json()) // Convertimos la respuesta a JSON
        .then(data => {
            if (data.success) { 
                // Si el comentario se guardó correctamente, lo agregamos a la lista sin recargar

                let comentariosContainer = document.getElementById("comentariosContenedor"); // Contenedor de comentarios
                
                let nuevoComentario = document.createElement("div"); // Creamos un nuevo div para el comentario
                nuevoComentario.classList.add("row", "mt-3"); // Agregamos clases para darle estilo

                // Agregamos el contenido del comentario en formato HTML
                nuevoComentario.innerHTML = `
                    <div class="col-12">
                        <div class="mb-3">
                            <b>{{Auth::user()->name}}</b> <br>
                            <p class="bg-gray border py-2">${data.comentario}</p>
                            <small>Hace un momento</small>
                        </div>
                    </div>
                `;

                comentariosContainer.append(nuevoComentario); // Agregamos el nuevo comentario al inicio de la lista
                form.reset(); // Limpiamos el textarea después de enviar el comentario
            } 
            
            else {
                alert("Error al enviar el comentario." +  response.json()); // Si algo sale mal, mostramos una alerta
            }
        })
        .catch(error => alert("Error en AJAX:", error)); // Capturamos y mostramos errores en la consola
    });
});
</script>
    


<script>
    document.addEventListener("DOMContentLoaded", function() {
        $(function () {
    $('[data-bs-toggle="tooltip"]').tooltip()
})
    });
</script>