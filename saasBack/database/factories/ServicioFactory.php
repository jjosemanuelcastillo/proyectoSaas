<?php

namespace Database\Factories;

use App\Models\Empresa;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Servicio>
 */
class ServicioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'empresa_id' => Empresa::factory(),
            'nombre' => ucfirst(fake()->words(3, true)),
            'descripcion' => fake()->sentence(),
            'duracion_minutos' => fake()->randomElement([30, 45, 60, 90]),
            'precio' => fake()->randomFloat(2, 10, 80),
            'activo' => true,
        ];
    }
}
