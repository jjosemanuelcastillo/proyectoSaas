<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->restrictOnDelete();
            $table->foreignId('servicio_id')->constrained()->restrictOnDelete();
            $table->foreignId('empleado_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->dateTime('inicio');
            $table->dateTime('fin');
            $table->decimal('precio', 8, 2);
            $table->enum('estado', ['pendiente', 'confirmada', 'completada', 'cancelada', 'no_presentado'])->default('pendiente');
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index(['empleado_id', 'inicio']);
            $table->index(['empresa_id', 'inicio']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
