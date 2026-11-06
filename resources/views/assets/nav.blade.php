<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom border-3 sticky-top">
    <div class="container-fluid">
        <a href="{{route('perfil.admin')}}" class="navbar-brand fw-bold {{ request()->routeIs('perfil.admin') ? 'negro' : '' }}">
            <i class="fa fa-desktop me-1"></i> Inicio
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin" aria-controls="navbarAdmin" aria-expanded="false" aria-label="Menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarAdmin">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                {{-- <li class="nav-item">
                    <a href="{{route('gestionar.publicaciones')}}" class="nav-link fw-bold">
                        <i class="fa fa-newspaper me-1"></i> Publicaciones
                    </a>
                </li> --}}
                {{-- <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle fw-bold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-laptop me-1"></i> Gestionar Dispositivos
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="{{route('add_pc')}}" class="dropdown-item fw-bold"><i class="fa fa-desktop me-1"></i> Agregar PC</a></li>
                        <li><a href="{{route('add_printer')}}" class="dropdown-item fw-bold"><i class="fa fa-print me-1"></i> Agregar Impresoras</a></li>
                        <li><a href="{{route('add.telefono')}}" class="dropdown-item fw-bold"><i class="fa fa-charging-station me-1"></i> Agregar Teléfono</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a href="{{route('lista.dispositivos')}}" class="dropdown-item fw-bold"><i class="fa fa-list me-1"></i> Lista de dispositivos</a></li>
                    </ul>
                </li> --}}
                <li class="nav-item">
                    <a href="{{route('agregar.usuarios')}}" class="nav-link fw-bold {{ request()->routeIs('agregar.usuarios') ? 'active negro' : '' }}">
                        <i class="fa fa-user me-1"></i> Usuarios
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{route('admin.pedidos.tintas')}}" class="nav-link fw-bold {{ request()->routeIs('admin.pedidos.tintas') ? 'active negro' : '' }}">
                        <i class="fa fa-palette me-1"></i> Pedidos Tintas
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{route('admin.soporte')}}" class="nav-link fw-bold {{ request()->routeIs('admin.soporte') ? 'active negro' : '' }}">
                        <i class="fa fa-laptop-medical me-1"></i> Soporte Técnico
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{route('admin.soporte.agentes')}}" class="nav-link fw-bold {{ request()->routeIs('admin.soporte.agentes*') ? 'active negro' : '' }}">
                        <i class="fa fa-headset me-1"></i> Agentes de Soporte
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{route('admin.proveedores')}}" class="nav-link fw-bold {{ request()->routeIs('admin.proveedores*') ? 'active negro' : '' }}">
                        <i class="fa fa-truck me-1"></i> Proveedores
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{route('admin.evaluaciones')}}" class="nav-link fw-bold {{ request()->routeIs('admin.evaluaciones*','admin.secciones*','admin.evaluaciones.proveedores*') ? 'active negro' : '' }}">
                        <i class="fa fa-clipboard-list me-1"></i> Evaluaciones
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{route('admin.resultados.proveedores')}}" class="nav-link fw-bold {{ request()->routeIs('admin.resultados*') ? 'active negro' : '' }}">
                        <i class="fa fa-chart-bar me-1"></i> Resultados
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{route('admin.configuracion')}}" class="nav-link fw-bold {{ request()->routeIs('admin.configuracion*','admin.config.*') ? 'active negro' : '' }}">
                        <i class="fa fa-cog me-1"></i> Configuración
                    </a>
                </li>
            </ul>
            <form action="{{route('cerrar.session.admin')}}" method="POST" id="form-cerrar-session" class="d-flex">
                @csrf
                <button type="submit" id="cerrar_session" class="btn btn-outline-secondary btn-sm">
                    <i class="fa fa-power-off me-1"></i> Cerrar Sesión
                </button>
            </form>
        </div>
    </div>
</nav>
