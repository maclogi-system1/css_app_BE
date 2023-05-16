<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class Company extends Model
{
    use HasApiTokens, HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'name', 'password',
    ];

    protected $hidden = [
        'password',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
