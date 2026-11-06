{{--
    Renderiza la descripción del reporte como HTML.

    Se imprime sin escapar a propósito, que es lo que hace que se vean las
    negritas, listas y demás formato. El HTML ya viene saneado por
    TextoEnriquecido (HTMLPurifier) en ticketsController@reporte, que es lo
    único que garantiza que esto sea seguro. Si algún día se guarda una
    descripción sin pasar por ahí, esto pasa a ser un XSS.
--}}
@if (!empty($descripcion))
    <div class="descripcion-enriquecida">{!! $descripcion !!}</div>
@endif
