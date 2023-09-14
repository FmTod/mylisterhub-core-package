<?php

use Illuminate\Database\Eloquent\Factories\Factory;

class ImageFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->realText(15),
            'source' => $this->faker->imageUrl(),
            'width' => $this->faker->numerify(),
            'height' => $this->faker->numerify(),
        ];
    }
}
