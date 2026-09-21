<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BemEncontrado extends Model
{
    protected $table = 'bem_encontrados';

    protected $fillable = [
        'rp',
        'descricao',
        'situacao',
        'local',
        'encontrado_por_id',
        'status',
        'foto',
    ];

    public function encontradoPor()
    {
        return $this->belongsTo(User::class, 'encontrado_por_id');
    }
}
