<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class error_operation extends Model
{
    protected $table = 'error_operation';
    protected $fillable = [
        'operation',
        'message',
    ];
}
