<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Swift_IoException;
use Swift_TransportException;
use Throwable;

class CorreoEnvio extends Model
{
    public const ESTADO_ENVIADO = 'enviado';
    public const ESTADO_FALLIDO = 'fallido';

    protected $fillable = [
        'seccion', 'enviable_type', 'enviable_id', 'estado',
        'destinatarios', 'intentos', 'error', 'intento_en', 'entregado_en',
    ];

    protected $casts = [
        'intento_en'   => 'datetime',
        'entregado_en' => 'datetime',
    ];

    /**
     * El registro al que se notifico: un Reporte (soporte) o un Pedido (tintas).
     */
    public function enviable()
    {
        return $this->morphTo();
    }

    /**
     * Ejecuta el envio y deja constancia, exista o no excepcion.
     *
     * El reporte y los adjuntos ya estan guardados cuando esto corre, asi que
     * el unico trabajo razonable es no dejar que un fallo de red rompa la
     * peticion del usuario. Antes el ->send() iba sin try/catch y un timeout
     * de SMTP devolvia un 500 aunque el ticket habia quedado en la base.
     *
     * @param  callable  $envio  recibe los destinatarios ya armados y lanza si falla
     * @return array{ok:bool,error:?string,intentos:int}
     */
    public function intentar(string $seccion, array $destinatarios, callable $envio): array
    {
        $anterior = static::where('enviable_type', $this->enviable_type)
            ->where('enviable_id', $this->enviable_id)
            ->orderByDesc('id')
            ->first();

        $intentos = $anterior ? ((int) $anterior->intentos) + 1 : 1;

        $error = null;

        try {
            $envio();
        } catch (Swift_TransportException $e) {
            $error = $e->getMessage();
        } catch (Swift_IoException $e) {
            $error = $e->getMessage();
        } catch (Throwable $e) {
            // Cualquier otra cosa (plantilla rota, adjunto que no existe) tampoco
            // debe tumbar la peticion: el registro ya esta guardado.
            $error = get_class($e) . ': ' . $e->getMessage();
        }

        $this->fill([
            'seccion'       => $seccion,
            'estado'        => $error === null ? self::ESTADO_ENVIADO : self::ESTADO_FALLIDO,
            'destinatarios' => implode(', ', $destinatarios),
            'intentos'      => $intentos,
            'error'         => $error,
            'intento_en'    => now(),
            'entregado_en'  => $error === null ? now() : null,
        ])->save();

        return ['ok' => $error === null, 'error' => $error, 'intentos' => $intentos];
    }

    /**
     * El ultimo intento de este registro: null si nunca se notifico.
     */
    public static function ultimoPara($enviable): ?self
    {
        return static::where('enviable_type', $enviable->getMorphClass())
            ->where('enviable_id', $enviable->getKey())
            ->orderByDesc('id')
            ->first();
    }

    /**
     * De un lote de ids, cuales tienen el ultimo intento fallido. La vista lo
     * usa para pintar el boton de reenvio solo donde el correo quedo en el aire.
     */
    public static function idsConUltimoFallo(string $tipo, array $ids): array
    {
        if (! $ids) {
            return [];
        }

        $envios = static::where('enviable_type', $tipo)
            ->whereIn('enviable_id', $ids)
            ->orderBy('id')
            ->get();

        $ultimos = $envios->groupBy('enviable_id')->map->last();

        return $ultimos->filter(function ($envio) {
            return $envio->estado === self::ESTADO_FALLIDO;
        })->map(function ($envio, $enviableId) {
            return (int) $enviableId;
        })->values()->all();
    }
}
