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
        Schema::create('conferencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventario_id')->constrained('inventarios');
            $table->foreignId('rp_id')->constrained('bens');
            $table->foreignId('local_id')->constrained('locais');
            $table->string('situacao')->nullable();
            $table->string('observacao')->nullable();
            $table->timestamp('conferido_em')->nullable();
            $table->foreignId('conferido_por_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
            // Índices para otimização de consultas
            $table->index('situacao');
            $table->index('local_id');
            $table->index('rp_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conferencias');
    }
};
