<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['empresa_id', 'nombre', 'descripcion', 'duracion_minutos', 'precio', 'activo'])]
class Servicio extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'duracion_minutos' => 'integer',
            'precio' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function empleados(): BelongsToMany
    {
        return $this->belongsToMany(Empleado::class);
    }

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }
}
