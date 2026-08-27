<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ReaccionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'reaccion' => $this->faker->randomElement(['like', 'dislike', 'loveit'])
        ];
    }
}
