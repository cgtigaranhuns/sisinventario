<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements LdapAuthenticatable
{
    use AuthenticatesWithLdap, HasFactory, Notifiable, HasRoles;

    /** @use HasFactory<UserFactory> */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'guid',
        'domain',
        'local_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'local_id' => 'array',
        ];
    }

    public function local()
    {
        return $this->belongsTo(Local::class, 'local_id');
    }
}
