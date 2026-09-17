<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Local extends Model
{
    protected $table = 'locais';

    protected $fillable = [
        'nome',
        'descricao',
        'responsavel',
        'uorg',
    ];

    public function conferencias()
    {
        return $this->hasMany(Bem::class, 'local_id', 'id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
