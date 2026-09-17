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
            $table->text('descricao');
            $table->foreignId('local_id')->nullable()->constrained('locais');
            $table->string('ultima_situacao')->nullable();
            $table->string('elemento_despesa')->nullable();
            $table->decimal('valor', 10, 2)->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();
            // Índices para otimização de consultas
            $table->index('rp');
            $table->index('local_id');
            $table->index('ultima_situacao');
            $table->index('elemento_despesa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bens');
    }
};
