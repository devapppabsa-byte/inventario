@extends('layout')
@section('title', 'Muro')
@section('contenido')
@include('user.cabecera')


{{-- boton flotante con su modal --}}
<button class="btn btn-dark  flotante" data-bs-toggle="modal" data-bs-target="#publicar">
    <i class="fa fa-plus "></i>
</button>

<!-- Modal -->
<div class="modal fade" id="publicar" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered ">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="exampleModalLabel">
            <i class="fa-solid fa-bullhorn"></i>
            Crear nueva publicación
        </h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body px-0">
        <form action="{{route('post.user.store')}}" method="post">
            @csrf
            <div class="col-12 ">
                
                <div id="toolbar">
                        <select class="ql-font"></select>
                        <button class="ql-bold"></button>
                        <button class="ql-italic"></button>
                        <button class="ql-underline"></button>
                        <button class="ql-link"></button>
                 </div>
                    
                    <div id="articulo" class="ql-editor"></div>
                    <input type="hidden"  name="post_body" value="{{old('post_body')}}" id="contenido">
                    <button class="btn btn-dark my-2  btn-sm">
                        <i class="fa fa-paper-plane me-2"></i>
                        Publicar
                    </button>
                </div>
        </form>        
      </div>

    </div>
  </div>
</div>





<div class="container ">



    <div class="row mt-2 border p-3 mt-4">

        <div class="col-12">

            @forelse ($postUsers  as $post)
                <div class="row p-4 mt-4 bg-white shadow shadown-sm">
                    <div class="col-12">
                        <h4 class="p-0 fw-bold">{{$post->user->name}}</h4>
                        <i class="p-0 italic"> {{$post->created_at}}</i>
                    </div>
                    <div class="col-12 border rounded ">
                        <p class="">{!!$post->post_body!!}</p>
                    </div>
                    

                    <div class="col-12 py-3">
                        <div class="btn-group">
                            <button class="btn btn-primary btn-sm reaction-btn " data-reaction="like" data-post-id="{{$post->id}}">
                                <i class="fa fa-thumbs-up"></i>
                                <span class="dislike-count">{{$post->likeCount()}}</span>
                            </button>
                            <button class="btn btn-danger btn-sm reaction-btn " data-reaction="dislike" data-post-id="{{$post->id}}">
                                <i class="fa fa-thumbs-down"></i>
                                <span class="like-count">{{$post->dislikeCount()}}</span>
                            </button>
                        </div>
                    </div>


                    <div class="col-12 mt-2">
                        <div class="row d-flex align-items-center justify-content-center">
                            <div class="col-12 px-0">
                                <h5 class="font-arimo">Comenta: </h5>
                            </div>
                            <div class="col-12 col-sm-12 col-md-12 col-lg-11 px-0 mx-0">
                                <textarea name="" class="form-control w-100 comentario_user" placeholder="Escribe un comentario para esta publicación..." ></textarea>
                            </div>
                            <div class="col-12 col-sm-12 col-md-12 col-lg-1 px-2 mx-0 mt-md-3 mt-lg-0">
                                <button class="btn btn-dark rounded rounded-pill">Comentar</button>
                            </div>
                        </div>
                    </div>                   

                    <div class="col-12 mt-3 bg-light py-3">
                        <a class="text-dark h5 fw-bold" data-bs-toggle="collapse" href="#p{{$post->id}}" role="button" aria-expanded="false" aria-controls="collapseExample">
                        Todos los comentarios
                        </a>

                        <div class="collapse mt-3" id="p{{$post->id}}">

                            <div class="card card-body border-0 mt-2">
                                <h6 class=""> <u> Segundo Usuario Dice: </u></h6>
                                Some placeholder content for the collapse component. This panel is hidden by default but revealed when the user activates the relevant trigger.
                            </div>   

                        </div>
                    </div>

                </div>
                <hr>
            @empty

                <div class="row justify-content-center ">
                    <div class="col-8 text-center">
                        <h1>
                            <i class="fa fa-magic me-3 text-center p-5" ></i>
                            No hay publicaciones para mostrar
                        </h1>
                    </div>
                </div>

            @endforelse







        </div>
    </div>




</div>
    





<script>

document.querySelectorAll('.reaction-btn').forEach( btn => {


    btn.addEventListener('click', function(){
        
        const pstId = btn.dataset.postId;
        const reaction = btn.satase.reaction;

        
        fetch(`/post/${postId}/reaction`, {
            
            method:POST,
            headers:{
                'Content-Type': 'aplication/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({reaction})
        })
        .then(res => res.json())
        .then(data => {

            //Actualizar los controladores 
            const postDiv = document.querySelector(`.post[data-post-id="${postId}"]`);
            postDiv.querySelector('.like-count').textContent = data.likes;
            postDiv.querySelector('.dislike-count').textContent = data.dislikes;

        });



    })


})



</script>



<script>
    const quill = new Quill('#articulo', {
      theme: 'snow',
      modules:{
        toolbar: '#toolbar',
        

      }
    });
  
    quill.on('text-change', function(){
      document.getElementById('contenido').value = quill.root.innerHTML;
    })
  
  </script>

@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    gsap.from('.row.p-4.mt-4', { opacity: 0, y: 40, duration: 0.6, stagger: 0.12, ease: 'power2.out', delay: 0.3 });
    gsap.from('.flotante', { scale: 0, opacity: 0, duration: 0.5, ease: 'back.out(1.7)', delay: 0.8 });
  });
</script>
@endsection

