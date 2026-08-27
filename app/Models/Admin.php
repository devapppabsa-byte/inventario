<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;


class Admin extends Authenticatable implements AuthenticatableContract
{
    use HasFactory;

    protected $table = 'admin';
    protected $fillable = [];
}
