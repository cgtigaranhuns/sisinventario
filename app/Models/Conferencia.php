<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conferencia extends Model
{
    protected $table = 'conferencias';

    protected $fillable = [
        'inventario_id',
        'rp_id',
        'local_id',
        'situacao',
        'observacao',
        'conferido_em',
        'conferido_por_id',
    ];

    protected $casts = [
        'conferido_em' => 'datetime',
    ];

    public function inventario()
    {
        return $this->belongsTo(Inventario::class, 'inventario_id');
    }

    public function bem()
    {
        return $this->belongsTo(Bem::class, 'rp_id');
    }

    public function local()
    {
        return $this->belongsTo(Local::class, 'local_id');
    }

    public function conferidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'conferido_por_id');
    }
}
