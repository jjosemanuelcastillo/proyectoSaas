<?php

namespace Database\Factories;

use App\Models\Empleado;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Empleado>
 */
class EmpleadoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->empleado(),
            'empresa_id' => Empresa::factory(),
            'activo' => true,
        ];
    }
}
