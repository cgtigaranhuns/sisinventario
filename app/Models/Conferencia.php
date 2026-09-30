<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Conferencia extends Model
{
    use LogsActivity;

    protected $table = 'conferencias';

    protected $fillable = [
        'inventario_id',
        'rp_id',
        'local_id',
        'situacao',
        'conferido_em',
        'conferido_por_id',
    ];

    protected $casts = [
        'conferido_em' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'inventario_id',
                'rp_id',
                'local_id',
                'situacao',
                'conferido_em',
                'conferido_por_id',
            ])
            ->logOnlyDirty();
    }

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
