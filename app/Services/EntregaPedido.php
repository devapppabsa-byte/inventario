<?php

namespace App\Services;

use App\Models\Pedido;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class EntregaPedido
{
    /**
     * Marca un pedido como entregado. Si la entrega fue parcial se genera un
     * pedido residual unicamente con lo que falto, para que tambien se pueda
     * marcar como completado despues.
     *
     * @return array{ok:bool,aviso?:string,errores?:array,mensaje?:string,correo?:array}
     */
    public static function marcar(Pedido $pedido, array $datos, string $quien): array
    {
        if ($pedido->status !== null && strcasecmp($pedido->status, 'pendiente') !== 0) {
            return ['ok' => true, 'aviso' => 'El pedido ya estaba marcado como completado'];
        }

        $esCintas = $pedido->numero === 'Cintas';
        $insumos = json_decode($pedido->colores, true);
        $insumos = is_array($insumos) ? $insumos : [];

        $reglas = ['entrega' => 'required|in:total,parcial'];
        $mensajes = [
            'entrega.required' => 'Indica si se entregaron todas las tintas.',
            'entrega.in'       => 'Indica si se entregaron todas las tintas.',
        ];

        $total = 0;
        $permitidos = [];

        if ($esCintas) {
            $total = (int) ($insumos[0] ?? 0);

            if ($total < 1) {
                return ['ok' => false, 'errores' => ['entrega' => 'No se pudo determinar la cantidad de cintas de este pedido.']];
            }

            $reglas['cantidad_pendiente'] = 'required_if:entrega,parcial|integer|min:1|max:'.$total;
            $mensajes['cantidad_pendiente.required_if'] = 'Indica cuantas cintas quedaron pendientes.';
            $mensajes['cantidad_pendiente.integer'] = 'La cantidad de cintas pendientes debe ser un numero.';
            $mensajes['cantidad_pendiente.min'] = 'Deben quedar al menos 1 cinta pendiente.';
            $mensajes['cantidad_pendiente.max'] = 'No pueden quedar mas cintas de las pedidas ('.$total.').';
        } else {
            $permitidos = array_values($insumos);
            $reglas['pendientes'] = 'required_if:entrega,parcial|array|min:1';
            $reglas['pendientes.*'] = 'required|string|distinct';
            $mensajes['pendientes.required_if'] = 'Marca las tintas que quedaron pendientes.';
            $mensajes['pendientes.array'] = 'Marca las tintas que quedaron pendientes.';
            $mensajes['pendientes.min'] = 'Marca al menos una tinta pendiente.';
            $mensajes['pendientes.*.distinct'] = 'Hay una tinta marcada mas de una vez.';
        }

        $validar = Validator::make($datos, $reglas, $mensajes);

        if ($validar->fails()) {
            return ['ok' => false, 'errores' => $validar->errors()->toArray()];
        }

        $entrega = $datos['entrega'];
        $pendientes = [];
        $cantidadPendiente = 0;

        if ($entrega === 'parcial') {
            if ($esCintas) {
                $cantidadPendiente = (int) $datos['cantidad_pendiente'];

                if ($cantidadPendiente >= $total) {
                    return ['ok' => false, 'errores' => ['cantidad_pendiente' => 'Si faltan todas las cintas el pedido no se entrego: dejalo como pendiente.']];
                }
            } else {
                $elegidos = array_values(array_unique((array) ($datos['pendientes'] ?? [])));
                $pendientes = array_values(array_intersect($permitidos, $elegidos));

                if ($pendientes === []) {
                    return ['ok' => false, 'errores' => ['pendientes' => 'Marca al menos una tinta pendiente.']];
                }

                if (count($pendientes) !== count($elegidos)) {
                    return ['ok' => false, 'errores' => ['pendientes' => 'Solo se pueden marcar tintas de este pedido.']];
                }

                if (count($pendientes) >= count($permitidos)) {
                    return ['ok' => false, 'errores' => ['pendientes' => 'Si faltan todas las tintas el pedido no se entrego: dejalo como pendiente.']];
                }
            }
        }

        $pedido->status = $entrega === 'total' ? 'Completado' : 'Entrega parcial';
        $pedido->fecha_entrega = substr(Carbon::now(), 0, 11);
        $pedido->completado_por = $quien;
        $pedido->save();

        $correo = null;

        if ($entrega === 'parcial') {
            $residual = new Pedido();
            $residual->numero = $pedido->numero;
            $residual->colores = $esCintas ? '['.$cantidadPendiente.']' : json_encode($pendientes);
            $residual->marca = $pedido->marca;
            $residual->cantidad = $pedido->cantidad;
            $residual->foto_tintas = $pedido->foto_tintas;
            $residual->user_id = $pedido->user_id;
            $residual->fecha_pedido = substr(Carbon::now(), 0, 10);
            $residual->parent_id = $pedido->id;
            $residual->save();

            $correo = NotificadorCorreo::tinta($residual);

            return [
                'ok' => true,
                'correo' => $correo,
                'mensaje' => 'Entrega parcial registrada y pedido residual generado',
            ];
        }

        self::autoCompletarPadres($pedido);

        return ['ok' => true, 'mensaje' => 'El pedido se marco como completado'];
    }

    /**
     * Cuando el ultimo residual pendiente se completa, el original deja de ser
     * parcial. Solo cambia el status: completado_por y fecha_entrega conservan
     * la ronda parcial.
     */
    private static function autoCompletarPadres(Pedido $pedido): void
    {
        $nivel = $pedido;

        while ($nivel->parent_id !== null) {
            $padre = $nivel->parent;

            if (! $padre || strcasecmp($padre->status, 'Entrega parcial') !== 0) {
                break;
            }

            $abiertos = $padre->hijos()
                ->where(function ($q) {
                    $q->whereNull('status')->orWhere('status', '!=', 'Completado');
                })
                ->count();

            if ($abiertos > 0) {
                break;
            }

            $padre->status = 'Completado';
            $padre->save();
            $nivel = $padre;
        }
    }
}
