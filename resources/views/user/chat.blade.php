
@extends('layout')
@section('title', 'Control de Accesos')
@section('contenido')
@include('user.cabecera')




<div id="content">
    <h2>Chat con {{ $receiver->name }}</h2>
    <div id="chat-box" style="height:300px; overflow-y:scroll; border:1px solid #ccc; padding:10px;"></div>

    <form id="message-form">
        @csrf
        <input type="hidden" name="receiver_id" value="{{ $receiver->id }}">
        <input type="text" name="content" placeholder="Escribe un mensaje..." required>
        <button type="submit">Enviar</button>
    </form>
</div>




<script>
    function loadMessages() {
        $.get('{{route("receiver", $receiver->id)}}', function(data) {
            $('#chat-box').html('');
                console.log("📦 RESPUESTA DEL BACKEND:", data); // 👈 Mira qué devuelve
            data.forEach(msg => {
                $('#chat-box').append(`<div><strong>${msg.sender_id == {{ auth()->id() }} ? 'Yo' : '{{ $receiver->name }}'}:</strong> ${msg.content}</div>`);
            });
        });
    }

    $('#message-form').submit(function(e) {
        e.preventDefault();
        $.post("{{route('sent')}}", $(this).serialize(), function() {
            loadMessages();
            $('input[name="content"]').val('');
        });
    });

    setInterval(loadMessages, 3000); // Recarga cada 3 segundos
    loadMessages();
</script> 



@endsection




