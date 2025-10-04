<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $table = 'users'; //  if using 'users' table

    protected $attributes = [
        'role' => 'admin',
    ];

    /**
     * The guard name used by Spatie permission.
     */
    protected string $guard_name = 'admin'; // 
}
