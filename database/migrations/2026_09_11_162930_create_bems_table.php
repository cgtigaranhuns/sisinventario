<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bens', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('rp')->unique()->comment('Número de patrimônio (RP)');
            $table->string('local')->nullable();
            $table->string('situacao')->nullable();
            $table->text('descricao');
            $table->string('observacao')->nullable();

            // Controle da conferência
            $table->timestamp('conferido_em')->nullable();
            $table->foreignId('conferido_por_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('situacao');
            $table->index('local');
            $table->index('rp');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bens');
    }
};