<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bem extends Model
{
    protected $table = 'bens';

    protected $fillable = [
        'rp',
        'descricao',
        'local_id',
        'ultima_situacao',
        'elemento_despesa',
        'valor',
        'observacao',
        'situacao',
        'conferido_em',
        'conferido_por_id',
    ];

    protected $casts = [
        'conferido_em' => 'datetime',
    ];

    public function local(): BelongsTo
    {
        return $this->belongsTo(Local::class, 'local_id');
    }

    public function conferencias(): HasMany
    {
        return $this->hasMany(Conferencia::class, 'rp_id');
    }
}
