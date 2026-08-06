<?php

namespace App\Models;

use Core\Model;

class User extends Model
{
    protected string $table = 'users';

    protected array $fillable = [
        'university_id',
        'department_id',
        'role_id',
        'matric_staff_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'gender',
        'profile_photo',
        'is_active',
        'last_login_at',
        'last_login_ip',
        'remember_token',
    ];

    protected array $hidden = [
        'password',
        'remember_token',
    ];

    public function getFullNameAttribute(): string
    {
        return trim(($this->attributes['first_name'] ?? '') . ' ' . ($this->attributes['last_name'] ?? ''));
    }

    public function isActive(): bool
    {
        return (bool) ($this->attributes['is_active'] ?? false);
    }
}
