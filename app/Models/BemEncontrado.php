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
        'local_id',
        'encontrado_por_id',
        'status',
        'foto',
        'sem_rp',
    ];

    public function encontradoPor()
    {
        return $this->belongsTo(User::class, 'encontrado_por_id');
    }

    public function local()
    {
        return $this->belongsTo(Local::class, 'local_id');
    }
}
