<?php

namespace App\Models;

use Core\Model;

class Faculty extends Model
{
    protected string $table = 'faculties';

    protected array $fillable = [
        'university_id',
        'name',
        'code',
        'dean_name',
    ];
}
