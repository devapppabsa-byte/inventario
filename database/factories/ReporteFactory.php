<?php

namespace Database\Factories;

use App\Models\Reporte;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReporteFactory extends Factory
{

    protected $model = Reporte::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {




        return [
            'dispositivo' => $this->faker->randomElement(['Samsung', 'Apple', 'Sony', 'LG']),
            'otro' => $this->faker->randomElement(['Red', 'Modem', 'Pantalla']),
            'descripcion' => $this->faker->text(),
            'status' => $this->faker->randomElement(['pendiente', 'completado']),
            'fecha_reporte'=> $this->faker->date(),
            'prioridad'=> $this->faker->randomElement(['Alta', 'Baja', 'Media']),
            'fecha_solucion'=> $this->faker->date(),
            'detalles_solucion'=> $this->faker->text(),
            'user_id'=> $this->faker->randomNumber()
        ];
    }
}
