<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    protected $table = 'admins';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'contact_number',
        'password',
    ];

    protected $hidden = [
        'password', 
    ];

    public $timestamps = true;
}
