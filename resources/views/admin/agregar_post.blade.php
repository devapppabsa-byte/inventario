@extends('layout')
@section('title', 'Genrar Post')
@section('contenido')
@include('assets.nav')   



<form action="{{route('post.store')}}" enctype="multipart/form-data" method="POST">
    @csrf
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12 text-center p-3 bg-white shadows m-3 ">
            <h3 class="fw-bold titulo_publicaciones">Agregar Publicación</h3>
            @if (session('publicacion_agregada'))
                <div class="alert alert-success">
                    {{session('publicacion_agregada')}}
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{$error}}</li>
                        @endforeach
                    </ul>
                </div>
                
            @endif
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-11 bg-white p-5">
            <div class="row">
                <div class="col-sm-12 col-md-12 col-lg-4">
                    <div class="mb-3">
                        <label for="fw-bold" class="fw-bold">Titulo de la publicación: </label>
                        <input type="text" name="titulo" class="form-control" id="titulo">
                    </div>
                </div>

                <div class="col-sm-12 col-md-12 col-lg-4">
                    <div class="mb-3">
                        <label for="" class="fw-bold">Categoria: </label>
                        <select name="categoria" id="" class="form-control">
                            <option value="tutoriales">Tutoriales</option>
                            <option value="articulos">Articulos</option>
                        </select>
                    </div>
                </div>

                <div class="col-sm-12 col-md-12 col-lg-4">
                    <div class="mb-3">
                        <label for="" class="fw-bold"">Link YouTube: </label>
                        <input type="text" class="form-control">
                    </div>
                </div>



                <div class="col-12">
                    <div class="mb-3">
                        <label for="fw-bold" class="fw-bold">Introducción: </label>
                        <textarea name="introduccion" class="form-control"  ></textarea>
                    </div>
                </div>

                <div class="col-12">
                    <label for="" class="fw-bold">Portada del articulo</label>
                    <div class="mb-3">
                        <input type="file" name="portada" class="form-control">
                    </div>
                </div>

                <div class="col-12 mb-4">
                    <div id="toolbar">
                        <select class="ql-font"></select>
                        <select class="ql-size"></select>
                        <button class="ql-bold"></button>
                        <button class="ql-italic"></button>
                        <button class="ql-underline"></button>
                        <button class="ql-strike"></button>
                        <select class="ql-color"></select>
                        <select class="ql-background"></select>
                        <button class="ql-blockquote"></button>
                        <button class="ql-code-block"></button>
                        <button class="ql-image"></button>
                    </div>

                    <div id="articulo" class="ql-editor"></div>
                    <input type="hidden"  name="cuerpo" value="{{old('cuerpo')}}" id="contenido">
                </div>



                <div class="col-sm-12 col-md-8 col-lg-4 mt-5">
                    <button class="btn btn-success">
                        <i class="fa fa-plus"></i>
                        Agregar Publicación
                    </button>
                </div>
            </div>
            
        </div>
    </div>
    
    
</div>

</form>



@endsection


@section('scripts')
<script>
    const quill = new Quill('#articulo', {
      theme: 'snow',
      modules:{
        toolbar: '#toolbar'
      }
    });
  
    quill.on('text-change', function(){
      document.getElementById('contenido').value = quill.root.innerHTML;
    })
  
  </script>
@endsection