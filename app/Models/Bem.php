<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Bem extends Model
{
    protected $table = 'bens';

    protected $fillable = [
        'rp',
        'local',
        'situacao',
        'descricao',
        'observacao',
        'conferido_em',
        'conferido_por_id',
    ];

    protected $casts = [
        'conferido_em' => 'datetime',
    ];

    public function conferidoPor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'conferido_por_id');
    }

    public function scopePendentes(Builder $query): Builder
    {
        return $query->whereNull('conferido_em');
    }

    public function scopeConferidos(Builder $query): Builder
    {
        return $query->whereNotNull('conferido_em');
    }

    public function scopePorLocal(Builder $query, string $local): Builder
    {
        return $query->where('local', $local);
    }

    protected static function booted(): void
    {
        // Sempre que o usuário alterar a situação ou o local pela tela de
        // conferência, o item é automaticamente marcado como "conferido",
        // sem exigir um clique extra — é isso que dá agilidade ao processo.
        static::updating(function (Bem $bem) {
            if ($bem->isDirty(['situacao', 'local']) && ! $bem->isDirty('conferido_em')) {
                $bem->conferido_em = now();
                $bem->conferido_por_id = Auth::id();
            }
        });
    }
}