<?php

namespace Database\Factories;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Empresa>
 */
class EmpresaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nombre = fake()->unique()->company();

        return [
            'user_id' => User::factory()->dueno(),
            'nombre' => $nombre,
            'slug' => Str::slug($nombre),
            'descripcion' => fake()->sentence(12),
            'telefono' => fake()->numerify('9## ### ###'),
            'email' => Str::slug($nombre).'@reservas.test',
            'direccion' => fake()->streetAddress(),
            'ciudad' => fake()->city(),
            'logo' => null,
            'activo' => true,
        ];
    }
}
