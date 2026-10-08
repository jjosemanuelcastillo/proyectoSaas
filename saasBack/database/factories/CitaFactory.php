<?php

namespace Database\Factories;

use App\Models\Cita;
use App\Models\Empleado;
use App\Models\Empresa;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Cita>
 */
class CitaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * El servicio y el empleado se crean en la misma empresa que la cita,
     * y el fin y el precio salen del servicio.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'empresa_id' => Empresa::factory(),
            'servicio_id' => fn (array $attributes) => Servicio::factory()->create(['empresa_id' => $attributes['empresa_id']])->id,
            'empleado_id' => fn (array $attributes) => Empleado::factory()->create(['empresa_id' => $attributes['empresa_id']])->id,
            'user_id' => User::factory(),
            'inicio' => Carbon::instance(fake()->dateTimeBetween('+1 day', '+14 days'))->setTime(fake()->numberBetween(9, 18), 0),
            'fin' => fn (array $attributes) => Carbon::parse($attributes['inicio'])->addMinutes(Servicio::find($attributes['servicio_id'])->duracion_minutos),
            'precio' => fn (array $attributes) => Servicio::find($attributes['servicio_id'])->precio,
            'estado' => 'pendiente',
            'notas' => null,
        ];
    }
}
