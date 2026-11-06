<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom border-3 sticky-top">
    <div class="container-fluid">
        <a href="{{route('soporte.index')}}" class="navbar-brand fw-bold {{ request()->routeIs('soporte.index') ? 'negro' : '' }}">
            <i class="fa fa-laptop-medical me-1"></i> Soporte Técnico
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSoporte" aria-controls="navbarSoporte" aria-expanded="false" aria-label="Menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSoporte">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a href="{{route('soporte.index')}}" class="nav-link fw-bold {{ request()->routeIs('soporte.index') ? 'active negro' : '' }}">
                        <i class="fa fa-list me-1"></i> Solicitudes
                    </a>
                </li>
            </ul>
            <span class="navbar-text fw-bold me-3">
                <i class="fa fa-user me-1"></i>{{ auth('soporte')->user()->nombre }}
            </span>
            <form action="{{route('soporte.cerrar_sesion')}}" method="POST" id="form-cerrar-sesion-soporte" class="d-flex">
                @csrf
                <button type="submit" id="cerrar_session_soporte" class="btn btn-outline-secondary btn-sm">
                    <i class="fa fa-power-off me-1"></i> Cerrar Sesión
                </button>
            </form>
        </div>
    </div>
</nav>
