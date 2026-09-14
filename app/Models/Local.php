<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Local extends Model
{
    protected $table = 'locais';

    protected $fillable = [
        'nome',
    ];

    public function conferencias()
    {
        return $this->hasMany(Bem::class, 'local', 'nome');
    }
}
