<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Proveedor;
use App\Models\Evaluacion;
use App\Models\EvaluacionUsuario;
use App\Models\EvaluacionProveedor;
use App\Models\EvaluacionSeccion;
use App\Models\EvaluacionPregunta;
use App\Models\EvaluacionOpcion;
use Illuminate\Database\Seeder;

class EncuestaProveedorTISeeder extends Seeder
{
    public function run()
    {
        // 1) Proveedor: usar el ya registrado; crearlo solo si la tabla esta vacia
        $proveedor = Proveedor::where('activo', true)->first() ?? Proveedor::first();

        if (!$proveedor) {
            $proveedor = Proveedor::create([
                'nombre'      => 'PCComputo',
                'descripcion' => 'Proveedor de TI que atiende telefonía, CCTV, soporte técnico y sistemas ASPEL',
                'contacto'    => 'Contacto PCComputo',
                'correo'      => 'contacto@pccomputo.mx',
                'telefono'    => '555-0000',
                'activo'      => true,
            ]);

            $this->command?->info('Proveedor PCComputo creado: no habia ninguno registrado.');
        }

        // 2) Evaluacion principal
        $evaluacion = Evaluacion::updateOrCreate(
            ['titulo' => 'EVALUACIÓN DE PROVEEDORES DE TI (USUARIOS)'],
            [
                'descripcion'  => 'Evaluación de los servicios del proveedor de TI por parte de los usuarios',
                'tipo_usuario' => 'Usuarios',
                'activo'       => true,
            ]
        );

        // 3) Vincular el proveedor a la evaluacion
        EvaluacionProveedor::updateOrCreate(
            ['evaluacion_id' => $evaluacion->id, 'proveedor_id' => $proveedor->id],
            ['activo' => true]
        );

        // 4) Definicion de la encuesta: secciones -> preguntas -> opciones
        // Total de puntos maximos: 6+8 + 6+6+4 + 10+10 + 8+12 + 15+15 = 100
        $secciones = [
            [
                'titulo'       => 'TELEFONÍA – RECEPCIONISTAS',
                'descripcion'  => 'Evaluación del servicio de telefonía a cargo del proveedor.',
                'periodicidad' => 'mensual',
                'preguntas'    => [
                    ['pregunta' => '¿Las llamadas son claras y sin interrupciones?', 'puntos_maximos' => 6],
                    ['pregunta' => '¿El servicio ha estado disponible sin caídas significativas?', 'puntos_maximos' => 8],
                ],
            ],
            [
                'titulo'       => 'PROVEEDOR DE CCTV',
                'descripcion'  => 'Evaluación del servicio de videovigilancia (CCTV).',
                'periodicidad' => 'trimestral',
                'preguntas'    => [
                    ['pregunta' => '¿Las imágenes de las cámaras son claras y de alta resolución?', 'puntos_maximos' => 6],
                    ['pregunta' => '¿Las cámaras funcionan sin interrupciones?', 'puntos_maximos' => 6],
                    ['pregunta' => '¿Se puede acceder fácilmente a las grabaciones cuando es necesario?', 'puntos_maximos' => 4],
                ],
            ],
            [
                'titulo'       => 'SOPORTE TÉCNICO A USUARIOS',
                'descripcion'  => 'Evaluación del soporte técnico brindado por el proveedor de sistemas externo.',
                'periodicidad' => 'trimestral',
                'preguntas'    => [
                    ['pregunta' => '¿El proveedor de sistemas externo responde y soluciona tus peticiones rápidamente?', 'puntos_maximos' => 10],
                    ['pregunta' => '¿Estás satisfecho con el servicio de soporte recibido?', 'puntos_maximos' => 10],
                ],
            ],
            [
                'titulo'       => 'SOPORTE TÉCNICO A USUARIOS ASPEL',
                'descripcion'  => 'Evaluación del soporte técnico del sistema ASPEL.',
                'periodicidad' => 'semestral',
                'preguntas'    => [
                    ['pregunta' => '¿El proveedor personaliza el sistema de acuerdo a tus necesidades?', 'puntos_maximos' => 8],
                    ['pregunta' => '¿Estás satisfecho con el servicio de soporte brindado?', 'puntos_maximos' => 12],
                ],
            ],
            [
                'titulo'       => 'SUMINISTROS DE TECNOLOGÍA',
                'descripcion'  => 'Evaluación de la calidad y oportunidad de entrega de los suministros.',
                'periodicidad' => 'trimestral',
                'preguntas'    => [
                    ['pregunta' => '¿La calidad del hardware entregado es buena?', 'puntos_maximos' => 15],
                    ['pregunta' => '¿Los suministros se entregan dentro de los plazos establecidos?', 'puntos_maximos' => 15],
                ],
            ],
        ];

        $seccionOrden = 1;
        foreach ($secciones as $seccionData) {
            $seccion = EvaluacionSeccion::updateOrCreate(
                ['evaluacion_id' => $evaluacion->id, 'titulo' => $seccionData['titulo']],
                [
                    'descripcion'  => $seccionData['descripcion'],
                    'periodicidad' => $seccionData['periodicidad'],
                    'orden'        => $seccionOrden,
                    'activo'       => true,
                ]
            );

            $preguntaOrden = 1;
            foreach ($seccionData['preguntas'] as $preguntaData) {
                $max = (float) $preguntaData['puntos_maximos'];

                $pregunta = EvaluacionPregunta::updateOrCreate(
                    ['seccion_id' => $seccion->id, 'pregunta' => $preguntaData['pregunta']],
                    [
                        'puntos_maximos' => $max,
                        'orden'          => $preguntaOrden,
                        'activo'         => true,
                    ]
                );

                $opciones = [
                    ['texto' => 'Sí cumple',    'puntos' => $max,     'valor' => 100, 'orden' => 1],
                    ['texto' => 'Parcialmente', 'puntos' => $max / 2, 'valor' => 50,  'orden' => 2],
                    ['texto' => 'No cumple',    'puntos' => 0,        'valor' => 0,   'orden' => 3],
                ];

                foreach ($opciones as $opcion) {
                    EvaluacionOpcion::updateOrCreate(
                        ['pregunta_id' => $pregunta->id, 'texto' => $opcion['texto']],
                        [
                            'puntos' => $opcion['puntos'],
                            'valor'  => $opcion['valor'],
                            'orden'  => $opcion['orden'],
                            'activo' => true,
                        ]
                    );
                }

                $preguntaOrden++;
            }

            $seccionOrden++;
        }

        // 5) Asignar la evaluacion a los usuarios existentes con acceso a todas sus secciones
        $seccionesIds = EvaluacionSeccion::where('evaluacion_id', $evaluacion->id)->pluck('id')->all();

        foreach (User::all() as $usuario) {
            EvaluacionUsuario::updateOrCreate(
                ['evaluacion_id' => $evaluacion->id, 'usuario_id' => $usuario->id],
                ['activo' => true, 'secciones' => $seccionesIds]
            );
        }
    }
}
