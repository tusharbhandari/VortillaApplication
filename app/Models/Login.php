<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Login extends Model
{
    use HasFactory;
    const CREATED_AT = 'active_form';
    const UPDATED_AT = 'active_thru';
}
