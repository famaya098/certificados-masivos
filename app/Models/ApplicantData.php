<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantData extends Model
{
    use HasFactory;

    protected $table = 'applicant_data';
    
    protected $fillable = [
        'get_first_unused_json',
        'get_first_unused_status',
        'get_first_unused_error',
        'overall_status'
    ];

    protected $casts = [
        'get_first_unused_json' => 'json',
    ];
}