@extends('layout')
@section('title', 'Home')
@section('contenido')
@include('user.cabecera')

<div class="container-fluid" id="content">

    <div class="row justify-content-center">
        <div class="col-7 text-center bg-white shadow-sm py-2">
            <h2 class="font-arimo">Seguridad Informática</h2>
        </div>
    </div>


    <div class="row justify-content-center mt-5 mx-2">

   
        @forelse ($publicaciones as $publicacion)
            <div class="col-sm-12 col-md-9 col-lg-2 bg-white shadow m-1">
                <div class="row justify-content-center">
                    <div class="col-12 p-0">
                        <img src="{{Storage::url($publicacion->portada)}}" class="img-fluid w-100" alt="">
                    </div>

                    <div class="col-12 m-2">
                        <h1 class="font-maker">{{$publicacion->titulo}}</h1>
                    </div>

                    <div class="col-12">
                        <p>
                            {{$publicacion->introduccion}}
                        </p>
                    </div>

                    <div class="col-12 m-3 text-center">
                        <a href="{{route('mostrar.post', $publicacion->id)}}" class="h5">Ir a la publicación</a>
                    </div>

                    <div class="col-12 text-center mt-1 p-3">
                        <form action="{{route('reaccion.post', $publicacion->id)}}" method="POST">
                            @csrf
                            <input type="hidden" name="publicacion_id" value="{{$publicacion->id}}">
                            <input type="hidde">
                            <div class="btn-group">
                                <button class="btn btn-danger d-block" name="reaccion" value="loveit">
                                    <i class="fa fa-heart text-white"></i>
                                    80
                                </button>
                                <button class="btn btn-primary d-block" name="reaccion" value="like">
                                    <i class="fa fa-thumbs-up"></i>
                                    30
                                </button>
                                <button class="btn btn-secondary d-block" name="reaccion" value="dislike">
                                    <i class="fa-solid fa-thumbs-down"></i>
                                    20
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <li>No hay nada</li>
        @endforelse


        




    </div>





</div>



@endsection    