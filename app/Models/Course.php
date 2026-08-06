<?php

namespace App\Models;

use Core\Model;

class Course extends Model
{
    protected string $table = 'courses';

    protected array $fillable = [
        'department_id',
        'code',
        'title',
        'description',
        'credit_unit',
        'level',
        'semester',
        'status',
    ];
}
