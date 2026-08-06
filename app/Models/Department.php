<?php

namespace App\Models;

use Core\Model;

class Department extends Model
{
    protected string $table = 'departments';

    protected array $fillable = [
        'faculty_id',
        'name',
        'code',
        'hod_name',
    ];
}
