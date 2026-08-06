<?php

namespace App\Models;

use Core\Model;

class University extends Model
{
    protected string $table = 'universities';

    protected array $fillable = [
        'name',
        'code',
        'domain',
        'logo_url',
        'address',
        'city',
        'state',
        'country',
        'status',
    ];
}
