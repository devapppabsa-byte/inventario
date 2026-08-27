<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AccesoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'nombre' => $this->faker->name(),
            'tipo' => $this->faker->randomElement(['Software', 'Sitio']) ,
            'status' => $this->faker->boolean(),
            'id_jefe' => $this->faker->randomDigit(),
            'justificacion' => $this->faker->text(20),
            'user_id' => $this->faker->randomNumber()
        ];
    }
}
