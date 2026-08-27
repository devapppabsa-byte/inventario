<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PedidoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'numero' => $this->faker->randomElement(['544', '504', '644']) ,
            'colores' => $this->faker->randomElement(['["amarillo"]', '[ "negro"]', '["azul"]']),
            'marca' => $this->faker->randomElement(['Epson', 'HP', 'Brother']),
            'cantidad' => $this->faker->randomNumber(),
            'respuesta_admin' => $this->faker->text(15),
            'status' => $this->faker->randomElement(['pendiente', 'completado']) , 
            'fecha_pedido' => $this->faker->date(),
            'fecha_entrega' => $this->faker->date(),
            'user_id' =>  $this->faker->randomNumber()
        ];
    }
}



// $table->string('numero');
// $table->json('colores');
// $table->string('marca')->nullable();
// $table->string('cantidad')->nullable();
// $table->string('status')->nullable()->default('pendiente');
// $table->string('fecha_pedido');
// $table->string('fecha_entrega')->nullable()->default('pendiente');
// $table->foreignId('user_id')->constrained('users')->onDelete('cascade');