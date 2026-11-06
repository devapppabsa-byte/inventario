<nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom d-print-none sticky-top">
    <div class="container-fluid">
        <span class="navbar-text cascadia">
            With <i class="fa fa-heart text-danger animate__animated animate__heartBeat"></i> by: <a href="#" target="_blank">Arturo Resendiz</a>
        </span>
        <div class="d-flex ms-auto">
            <form action="{{route('cerrar.session')}}" method="POST" class="d-inline">
                @csrf
                <button class="btn btn-outline-secondary btn-sm">
                    <i class="fa fa-power-off"></i> Cerrar sesión
                </button>
            </form>
        </div>
    </div>
</nav>

<div class="container-fluid d-print-none mt-2">

    <div class="row justify-content-center">

        <div class="col-12 col-lg-3 bg-white shadow-sm mx-1 p-3 py-4 border mb-2">
            <div class="row align-items-center">
                <div class="col-auto pe-2">
                    <i class="fa-solid fa-user-circle fa-3x text-warning"></i>
                </div>
                <div class="col" style="min-width:0">
                    <h3 class="m-0 p-0 text-truncate" title="{{Auth::user()->name}}">{{Auth::user()->name}}</h3>
                    <div class="text-break">{{Auth::user()->correo}}</div>
                    <div>
                        <span>{{Auth::user()->planta}}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8 bg-white shadow-sm p-3 text-center mb-2 border">
            <div class="row justify-content-center py-2">

                <div class="col-6 col-md-4 col-lg-auto mx-0 mx-md-2 text-center pt-2 zoom_menu">
                    <a href="{{route('directorio.show')}}">
                        <i class="fa-solid fa-book fa-3x {{ request()->path() == 'user/directorio' ? 'negro' : '' }}"></i>
                        <h5 class="{{ request()->path() == 'user/directorio' ? 'negro fw-bold' : '' }}">Directorio</h5>
                    </a>
                </div>

                @if (Auth::user()->impresoras)
                    <div class="col-6 col-md-4 col-lg-auto mx-0 mx-md-2 text-center pt-2 zoom_menu">
                        <a href="{{route('tintas.show')}}">
                            <i class="fa-solid fa-palette fa-3x {{ request()->path() == 'user/tintas' ? 'negro' : '' }}"></i>
                            <h5 class="{{ request()->path() == 'user/tintas' ? 'negro fw-bold' : '' }}">Tintas</h5>
                        </a>
                    </div>
                @endif

                <div class="col-6 col-md-4 col-lg-auto mx-0 mx-md-2 text-center pt-2 zoom_menu">
                    <a href="{{route('tickets.show')}}">
                        <i class="fa-solid fa-laptop-medical fa-3x {{ Request::is('user/tickets*') ? 'negro fw-bold' : '' }}"></i>
                        <h5 class="{{ Request::is('user/tickets*') ? 'negro fw-bold' : '' }}">Soporte Técnico</h5>
                    </a>
                </div>

                <div class="col-6 col-md-4 col-lg-auto mx-0 mx-md-2 text-center pt-2 zoom_menu">
                    <a href="{{route('evaluaciones.mis')}}">
                        <i class="fa-solid fa-clipboard-list fa-3x {{ Request::is('user/evaluaciones*') ? 'negro fw-bold' : '' }}"></i>
                        <h5 class="{{ Request::is('user/evaluaciones*') ? 'negro fw-bold' : '' }}">Evaluaciones</h5>
                    </a>
                </div>

                {{-- @if (Auth::user()->jefe)
                    <div class="col-6 col-md-4 col-lg-auto mx-0 mx-md-2 text-center pt-2 zoom_menu">
                        <a href="{{route('permisos.show')}}">
                            <i class="fa-solid fa-users-rectangle fa-3x {{ Request::is('user/permisos') ? 'negro fw-bold' : '' }}"></i>
                            <h5 class="{{ Request::is('user/permisos') ? 'negro fw-bold' : '' }}">Permisos</h5>
                        </a>
                    </div>
                @endif --}}

            </div>
        </div>
    </div>

</div>
