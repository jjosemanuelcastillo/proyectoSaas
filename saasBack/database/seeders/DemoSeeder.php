<?php

namespace Database\Seeders;

use App\Models\Cita;
use App\Models\Empleado;
use App\Models\Empresa;
use App\Models\Horario;
use App\Models\Servicio;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Datos de demostración: cuentas fijas para cada rol, tres negocios
 * con servicios reales y citas desde 30 días atrás hasta 14 días adelante.
 *
 * Todas las cuentas usan la contraseña "password".
 */
class DemoSeeder extends Seeder
{
    private const DIAS_ATRAS = 30;

    private const DIAS_ADELANTE = 14;

    /** Probabilidad de que un hueco libre acabe ocupado por una cita. */
    private const OCUPACION = 0.45;

    /** Tramos de trabajo por día de la semana (1 = lunes ... 7 = domingo). */
    private const HORARIO_SEMANAL = [
        1 => [['09:00', '14:00'], ['16:00', '20:00']],
        2 => [['09:00', '14:00'], ['16:00', '20:00']],
        3 => [['09:00', '14:00'], ['16:00', '20:00']],
        4 => [['09:00', '14:00'], ['16:00', '20:00']],
        5 => [['09:00', '14:00'], ['16:00', '20:00']],
        6 => [['09:00', '14:00']],
    ];

    public function run(): void
    {
        User::factory()->superadmin()->create([
            'name' => 'Admin Plataforma',
            'email' => 'admin@reservas.test',
        ]);

        $duenoDemo = User::factory()->dueno()->create([
            'name' => 'Laura Martín',
            'email' => 'dueno@reservas.test',
        ]);

        $empleadoDemo = User::factory()->empleado()->create([
            'name' => 'Carlos Ruiz',
            'email' => 'empleado@reservas.test',
        ]);

        $clienteDemo = User::factory()->create([
            'name' => 'Pedro García',
            'email' => 'cliente@reservas.test',
        ]);

        $clientes = User::factory(25)->create();

        $negocios = [
            [
                'nombre' => 'Peluquería Laura Martín',
                'ciudad' => 'Sevilla',
                'descripcion' => 'Peluquería unisex en el centro de Sevilla. Cortes, color y peinados para eventos.',
                'dueno' => $duenoDemo,
                'empleados' => [$duenoDemo, $empleadoDemo, User::factory()->empleado()->create()],
                'servicios' => [
                    ['Corte de pelo mujer', 45, 22.00],
                    ['Corte de pelo hombre', 30, 14.00],
                    ['Tinte', 90, 45.00],
                    ['Mechas', 120, 65.00],
                    ['Peinado', 30, 18.00],
                ],
            ],
            [
                'nombre' => 'Fisioterapia Avanza',
                'ciudad' => 'Madrid',
                'descripcion' => 'Clínica de fisioterapia deportiva y traumatológica.',
                'dueno' => User::factory()->dueno()->create(),
                'empleados' => User::factory(2)->empleado()->create()->all(),
                'servicios' => [
                    ['Valoración inicial', 30, 25.00],
                    ['Sesión de fisioterapia', 60, 40.00],
                    ['Masaje descontracturante', 45, 35.00],
                    ['Punción seca', 30, 30.00],
                ],
            ],
            [
                'nombre' => 'Centro de Estética Lumen',
                'ciudad' => 'Valencia',
                'descripcion' => 'Tratamientos faciales, manicura y depilación láser.',
                'dueno' => User::factory()->dueno()->create(),
                'empleados' => User::factory(2)->empleado()->create()->all(),
                'servicios' => [
                    ['Limpieza facial', 60, 45.00],
                    ['Manicura', 45, 20.00],
                    ['Pedicura', 60, 28.00],
                    ['Depilación láser', 30, 50.00],
                ],
            ],
        ];

        foreach ($negocios as $negocio) {
            $empresa = $this->crearEmpresa($negocio);
            $servicios = $this->crearServicios($empresa, $negocio['servicios']);
            $empleados = $this->crearEmpleados($empresa, $negocio['empleados']);

            $this->asignarServicios($empleados, $servicios);
            $this->crearHorarios($empleados);
            $this->crearCitas($empresa, $empleados, $clientes, $clienteDemo);
        }
    }

    private function crearEmpresa(array $negocio): Empresa
    {
        return Empresa::factory()->create([
            'user_id' => $negocio['dueno']->id,
            'nombre' => $negocio['nombre'],
            'slug' => Str::slug($negocio['nombre']),
            'descripcion' => $negocio['descripcion'],
            'email' => Str::slug($negocio['nombre']).'@reservas.test',
            'ciudad' => $negocio['ciudad'],
        ]);
    }

    /**
     * @param  array<int, array{0: string, 1: int, 2: float}>  $catalogo
     * @return Collection<int, Servicio>
     */
    private function crearServicios(Empresa $empresa, array $catalogo): Collection
    {
        return collect($catalogo)->map(fn (array $servicio) => Servicio::factory()->create([
            'empresa_id' => $empresa->id,
            'nombre' => $servicio[0],
            'duracion_minutos' => $servicio[1],
            'precio' => $servicio[2],
        ]));
    }

    /**
     * @param  array<int, User>  $usuarios
     * @return Collection<int, Empleado>
     */
    private function crearEmpleados(Empresa $empresa, array $usuarios): Collection
    {
        return collect($usuarios)->map(fn (User $usuario) => Empleado::create([
            'user_id' => $usuario->id,
            'empresa_id' => $empresa->id,
        ]));
    }

    /**
     * Cada servicio lo hace al menos un empleado, y cada empleado hace
     * al menos dos servicios.
     */
    private function asignarServicios(Collection $empleados, Collection $servicios): void
    {
        $asignados = $empleados->mapWithKeys(fn (Empleado $empleado) => [$empleado->id => collect()]);

        foreach ($servicios as $servicio) {
            $asignados[$empleados->random()->id]->push($servicio->id);
        }

        foreach ($empleados as $empleado) {
            $extra = $servicios->random(min(2, $servicios->count()))->pluck('id');
            $empleado->servicios()->sync($asignados[$empleado->id]->merge($extra)->unique()->values());
        }
    }

    private function crearHorarios(Collection $empleados): void
    {
        foreach ($empleados as $empleado) {
            foreach (self::HORARIO_SEMANAL as $dia => $tramos) {
                foreach ($tramos as [$inicio, $fin]) {
                    Horario::create([
                        'empleado_id' => $empleado->id,
                        'dia_semana' => $dia,
                        'hora_inicio' => $inicio,
                        'hora_fin' => $fin,
                    ]);
                }
            }
        }
    }

    /**
     * Recorre el horario de cada empleado día a día y va llenando huecos.
     * Como cada cita empieza donde acaba la anterior, nunca se solapan
     * y siempre caen dentro del horario.
     */
    private function crearCitas(Empresa $empresa, Collection $empleados, Collection $clientes, User $clienteDemo): void
    {
        $ahora = CarbonImmutable::now();
        $hoy = $ahora->startOfDay();

        for ($offset = -self::DIAS_ATRAS; $offset <= self::DIAS_ADELANTE; $offset++) {
            $dia = $hoy->addDays($offset);
            $tramos = self::HORARIO_SEMANAL[$dia->dayOfWeekIso] ?? [];

            foreach ($empleados as $empleado) {
                $servicios = $empleado->servicios;

                foreach ($tramos as [$horaInicio, $horaFin]) {
                    $cursor = $dia->setTimeFromTimeString($horaInicio);
                    $finTramo = $dia->setTimeFromTimeString($horaFin);

                    while ($cursor < $finTramo) {
                        $cabe = $servicios->filter(
                            fn (Servicio $servicio) => $cursor->addMinutes($servicio->duracion_minutos) <= $finTramo
                        );

                        if ($cabe->isEmpty() || fake()->randomFloat(2, 0, 1) > self::OCUPACION) {
                            $cursor = $cursor->addMinutes(30);

                            continue;
                        }

                        $servicio = $cabe->random();
                        $fin = $cursor->addMinutes($servicio->duracion_minutos);

                        Cita::create([
                            'empresa_id' => $empresa->id,
                            'servicio_id' => $servicio->id,
                            'empleado_id' => $empleado->id,
                            'user_id' => fake()->boolean(1) ? $clienteDemo->id : $clientes->random()->id,
                            'inicio' => $cursor,
                            'fin' => $fin,
                            'precio' => $servicio->precio,
                            'estado' => $this->estadoPara($fin, $ahora),
                        ]);

                        $cursor = $fin;
                    }
                }
            }
        }
    }

    private function estadoPara(CarbonImmutable $fin, CarbonImmutable $ahora): string
    {
        if ($fin < $ahora) {
            return fake()->randomElement([
                ...array_fill(0, 8, 'completada'),
                'cancelada',
                'no_presentado',
            ]);
        }

        return fake()->randomElement([
            ...array_fill(0, 6, 'confirmada'),
            ...array_fill(0, 3, 'pendiente'),
            'cancelada',
        ]);
    }
}
