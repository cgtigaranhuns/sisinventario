<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    protected $table = 'inventarios';

    protected $fillable = [
        'titulo',
        'data_inicio',
        'data_fim',
        'status',
        'comissao',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'comissao' => 'array',
    ];

    public function conferencias()
    {
        return $this->hasMany(Conferencia::class, 'inventario_id');
    }

    public function sincronizarBensConferidos(): void
    {
        $this->conferencias()
            ->whereNotNull('conferido_em')
            ->get(['rp_id', 'local_id', 'situacao', 'conferido_em', 'conferido_por_id'])
            ->each(function (Conferencia $conferencia): void {
                Bem::query()
                    ->whereKey($conferencia->rp_id)
                    ->update([
                        'local_id' => $conferencia->local_id,
                        'ultima_situacao' => $conferencia->situacao,
                        'conferido_em' => $conferencia->conferido_em,
                        'conferido_por_id' => $conferencia->conferido_por_id,
                    ]);
            });
    }
}