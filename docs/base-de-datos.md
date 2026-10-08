# Diseño de la base de datos

Plataforma SaaS de reservas multi-empresa. Backend en Laravel (API REST) con MySQL.

## Decisiones de diseño

- **Multi-tenant por columna:** todos los datos de un negocio cuelgan de `empresa_id`. Cada empresa solo ve sus propios empleados, servicios y citas.
- **Login único en `users`:** dueños, empleados y clientes son usuarios. El rol se guarda en `users.rol`.
- **Cliente registrado obligatorio:** toda cita tiene un `user_id`. Los datos de contacto del cliente se obtienen con la relación, no se duplican en la cita.
- **Horario de trabajo y citas son cosas distintas:** `horarios` guarda cuándo trabaja cada empleado cada semana; `citas` guarda cada reserva concreta.
- **El precio se copia en la cita:** si el servicio cambia de precio, las citas antiguas conservan el precio con el que se reservaron.
- **Nada se borra si tiene historial:** servicios, empleados y empresas se desactivan con `activo = false`.

## Tablas

### users

Tabla que trae Laravel, con dos columnas nuevas.

| Campo | Tipo | Notas |
|---|---|---|
| id | bigint | |
| name | string | |
| email | string | único |
| telefono | string | nuevo |
| rol | enum | `superadmin`, `dueno`, `empleado`, `cliente` (nuevo) |
| password | string | |
| email_verified_at | timestamp | nullable |
| remember_token | string | nullable |
| created_at / updated_at | timestamp | |

### empresas

| Campo | Tipo | Notas |
|---|---|---|
| id | bigint | |
| user_id | foreign key | → `users`, el dueño |
| nombre | string | |
| slug | string | único, para la URL `/negocio/{slug}` |
| descripcion | text | nullable |
| telefono | string | |
| email | string | |
| direccion | string | |
| ciudad | string | |
| logo | string | nullable, ruta de la imagen |
| activo | boolean | por defecto `true` |
| created_at / updated_at | timestamp | |

### empleados

Une a un usuario con una empresa. Sus datos personales y su login están en `users`.

| Campo | Tipo | Notas |
|---|---|---|
| id | bigint | |
| user_id | foreign key | → `users` |
| empresa_id | foreign key | → `empresas` |
| activo | boolean | por defecto `true` |
| created_at / updated_at | timestamp | |

Índice único: `(user_id, empresa_id)`.

### servicios

| Campo | Tipo | Notas |
|---|---|---|
| id | bigint | |
| empresa_id | foreign key | → `empresas` |
| nombre | string | |
| descripcion | text | nullable |
| duracion_minutos | integer | sirve para calcular el `fin` de la cita |
| precio | decimal(8,2) | |
| activo | boolean | por defecto `true` |
| created_at / updated_at | timestamp | |

### empleado_servicio

Tabla intermedia: qué empleado hace qué servicio.

| Campo | Tipo | Notas |
|---|---|---|
| id | bigint | |
| empleado_id | foreign key | → `empleados` |
| servicio_id | foreign key | → `servicios` |

Índice único: `(empleado_id, servicio_id)`.

### horarios

Horario semanal de trabajo. Un turno partido son dos filas el mismo día; un día sin filas es un día libre.

| Campo | Tipo | Notas |
|---|---|---|
| id | bigint | |
| empleado_id | foreign key | → `empleados` |
| dia_semana | tinyint | 1 = lunes … 7 = domingo |
| hora_inicio | time | |
| hora_fin | time | |
| created_at / updated_at | timestamp | |

### citas

| Campo | Tipo | Notas |
|---|---|---|
| id | bigint | |
| empresa_id | foreign key | → `empresas` |
| servicio_id | foreign key | → `servicios` |
| empleado_id | foreign key | → `empleados` |
| user_id | foreign key | → `users`, el cliente |
| inicio | datetime | |
| fin | datetime | `inicio` + `duracion_minutos` del servicio |
| precio | decimal(8,2) | precio del servicio al reservar |
| estado | enum | `pendiente`, `confirmada`, `completada`, `cancelada`, `no_presentado` |
| notas | text | nullable |
| created_at / updated_at | timestamp | |

Índice: `(empleado_id, inicio)`, para buscar rápido las citas de un empleado en un día.

## Relaciones

```
users ──1:N── empresas            (un dueño puede tener varios negocios)
users ──1:N── empleados           (una persona puede trabajar en varios negocios)
users ──1:N── citas               (un cliente tiene muchas citas)

empresas ──1:N── empleados
empresas ──1:N── servicios
empresas ──1:N── citas

empleados ──N:M── servicios       (a través de empleado_servicio)
empleados ──1:N── horarios
empleados ──1:N── citas

servicios ──1:N── citas
```

## Cálculo de huecos libres

1. Se leen los `horarios` del empleado para el día de la semana elegido.
2. Se leen sus `citas` de ese día que no estén canceladas.
3. Se recorren los tramos de trabajo en pasos de la `duracion_minutos` del servicio y se descartan los que se solapan con una cita.

Dos intervalos se solapan cuando `inicio_A < fin_B` y `inicio_B < fin_A`.

## Orden de las migraciones

Cada tabla depende de las anteriores:

1. `users` (modificar la existente)
2. `empresas`
3. `empleados`
4. `servicios`
5. `empleado_servicio`
6. `horarios`
7. `citas`

## Fuera del MVP

Se añadirán como tablas nuevas sin modificar estas:

- Reseñas de clientes
- Planes y suscripciones con Stripe
- Pago de señal al reservar
