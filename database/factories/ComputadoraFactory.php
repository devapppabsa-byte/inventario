<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ComputadoraFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'marca' =>      $this->faker->randomElement(['HP', 'DELL', 'BENQ']),
            'tipo' =>       $this->faker->randomElement(['AIO', 'Escritorio']),
            'modelo' =>     $this->faker->randomElement(['Inspiron 2020', 'OptiPlex']) ,
            'numero_serie' => $this->faker->text(10),
            'size_hdd' => $this->faker->randomNumber(),
            'ram' => $this->faker->randomNumber(),
            'estado' => $this->faker->randomElement(['Nuevo', 'Usado']),
            'accesorios' => $this->faker->randomElement(['Teclado', 'Mouse', 'Mouse y Teclado Inhalambricos']),
            'size_ssd' => $this->faker->randomNumber(),
            'procesador' => $this->faker->randomElement(['Intel Core I5 5432', 'AMD Ryzen 5 754888']) ,
            'SO' => $this->faker->randomElement(['Windows 10', 'Windows 11']),
            'user_id' => $this->faker->randomNumber(),
            'nuevo' => $this->faker->boolean(),
            'observaciones' => $this->faker->text(20) ,
            'imagen1' => $this->faker->randomElement(['https://picsum.photos/200/300', 'https://picsum.photos/200/300', 'https://picsum.photos/200/300']),
            'imagen2'=> $this->faker->randomElement(['https://picsum.photos/200/300', 'https://picsum.photos/200/300', 'https://picsum.photos/200/300']),
            'imagen3' => $this->faker->randomElement(['https://picsum.photos/200/300', 'https://picsum.photos/200/300', 'https://picsum.photos/200/300'])
        ];
    }
}
