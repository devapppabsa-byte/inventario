<?php

namespace App\Http\Controllers;

use App\Models\AdjuntoReporte;
use Illuminate\Support\Facades\Auth;

/**
 * Entrega los archivos de evidencia de un reporte.
 *
 * Los adjuntos viven fuera de la carpeta pública del disco 'local', así que
 * siempre se sirven por aquí y no por una URL adivinable. Solo los puede abrir
 * quien Hació el reporte, un administrador o un agente de soporte.
 */
class adjuntosController extends Controller
{
    public function show($id)
    {
        $adjunto = AdjuntoReporte::with('reporte')->findOrFail($id);

        if (!$this->tienePermiso($adjunto)) {
            abort(404);
        }

        if (!$adjunto->existeEnDisco()) {
            abort(404);
        }

        // Las imágenes se muestran en línea; el resto se descarga.
        return $adjunto->es_imagen
            ? response()->file($adjunto->rutaAbsoluta())
            : response()->download($adjunto->rutaAbsoluta(), $adjunto->nombre_original);
    }

    private function tienePermiso(AdjuntoReporte $adjunto): bool
    {
        if (Auth::guard('admin')->check() || Auth::guard('soporte')->check()) {
            return true;
        }

        $usuario = Auth::guard('web')->user();

        return $usuario && (int) $adjunto->reporte->user_id === (int) $usuario->id;
    }
}
