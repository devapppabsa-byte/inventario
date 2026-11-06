@extends('layout')
@section('title', 'Gestionar Publicaciones')
@section('contenido')
@include('assets.nav')


<div class="container-fluid">


    <div class="row justify-content-center mt-5">     
      
      <div class="col-10 my-3">
            <a href="{{route('agregar.post')}}" class="btn btn-dark">
                <i class="fa fa-plus"></i>
                Agregar Post
            </a>
        </div>

        <div class="col-10  bg-white p-0">
            <h2 class="p-3 fw-bold bg-dark text-white">Publicaciones realizadas</h2>
            <div class="tabla-responsive">
            <table class="table">
                <thead>
                  <tr class="text-uppercase">
                    <th scope="col">Titulo</th>
                    <th scope="col">Fecha</th>
                    <th scope="col">Autor</th>
                    <th scope="col">Acciones</th>
                  </tr>
                </thead>
                <tbody>

                  @forelse ($publicaciones as $publicacion)
                    <tr>
                      <th data-label="Titulo">{{$publicacion->titulo}}</th>
                      <td data-label="Fecha">{{$publicacion->created_at}}</td>
                      <td data-label="Autor" class="text-underline">
                        <i class="fa fa-pencil mx-2"></i>
                        {{$publicacion->autor}}
                      </td>
                      <td data-label="Acciones">
                        <div class="btn-group">
                          <button class="btn btn-danger">
                            <i class="fa fa-eraser"></i>
                          </button>
                          <button class="btn btn-dark">
                            <i class="fa fa-edit"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                  @empty
                      <li>No hay ni madres</li>
                  @endforelse
                  
                </tbody>
              </table>
            </div>
        </div>
    </div>



    <div class="row justify-content-center">
      <div class="col-10 mt-5 text-center text-white bg-dark p-3">
        <h2>Visitas a las publicaciones</h2>
      </div>
        <div class="col-10 bg-white">

          <div class="tabla-responsive">
          <table class="table">
            <thead>
              <tr>
                <th scope="col">#</th>
                <th scope="col">Usuario</th>
                <th scope="col">Publicacion</th>
                <th scope="col">Fecha</th>
              </tr>
            </thead>

            <tbody>

              @forelse ($visitas as $visita)
              <tr>
                <th data-label="#" scope="row">{{$visita->id}}</th>
                <td data-label="Usuario">{{$visita->user->name}}</td>
                <td data-label="Publicacion">{{$visita->publicacion->titulo}}</td>
                <td data-label="Fecha">{{$visita->created_at}}</td>
              </tr>
              @empty
              @endforelse

            </tbody>
          </table>              
          </div>
        </div>
    </div>



</div>


@endsection    
