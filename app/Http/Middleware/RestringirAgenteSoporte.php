<?php

namespace App\Http\Middleware;

use App\Models\AgenteSoporte;
use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Deja al agente de soporte unicamente en las rutas de soporte.
 *
 * El guard 'soporte' ya lo separa de las rutas auth:admin, pero hay rutas sin
 * middleware y podria aparecer una nueva. Aqui se cierra por omision: si hay
 * sesion de agente y la ruta no esta en la lista, se le cierra la sesion y se
 * le manda al login.
 *
 * Va en el grupo web y con prioridad antes de Authenticate (ver Kernel), porque
 * necesita la sesion ya iniciada y debe decidir antes de que el auth middleware
 * rechace la ruta.
 */
class RestringirAgenteSoporte
{
    /**
     * Rutas que el agente de soporte puede tocar.
     */
    public const RUTAS = [
        'login',              // ver la pantalla de ingreso
        'ingreso.admin',      // iniciar sesion
        'soporte.index',
        'soporte.exportar',
        'soporte.detalle',
        'soporte.comentario',
        'soporte.cerrar_sesion',
        // La evidencia de los reportes vive en una ruta compartida con usuarios y
        // admin. Sin esto, abrir un archivo cerraria la sesion del agente.
        'adjuntos.show',
    ];

    public function handle($request, Closure $next)
    {
        $agente = Auth::guard('soporte')->user();

        if (!$agente) {
            return $next($request);
        }

        // 'activo' solo se revisa al iniciar sesión, así que un agente que el
        // administrador desactivó seguiría entrando con la sesión ya abierta.
        // Se revalida en cada request para que desactivar sí corte el acceso.
        $sigueActivo = AgenteSoporte::find($agente->getAuthIdentifier());
        if (!$sigueActivo || !$sigueActivo->estaActivo()) {
            return $this->cerrarSesion($request, 'Tu cuenta de agente de soporte fue desactivada o eliminada. Contacta al administrador.');
        }

        if (in_array($request->route()?->getName(), self::RUTAS, true)) {
            return $next($request);
        }

        return $this->cerrarSesion($request, 'Tu cuenta de agente de soporte solo tiene acceso a las solicitudes de soporte.');
    }

    private function cerrarSesion($request, $mensaje)
    {
        Auth::guard('soporte')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('error', $mensaje);
    }
}
