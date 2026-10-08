<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['empleado_id', 'dia_semana', 'hora_inicio', 'hora_fin'])]
class Horario extends Model
{
    protected function casts(): array
    {
        return [
            'dia_semana' => 'integer',
        ];
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }
}
