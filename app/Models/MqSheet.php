<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MqSheet extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id', 'store_id', 'name', 'created_at', 'updated_at',
    ];

    public function mqAccountings(): HasMany
    {
        return $this->hasMany(MqAccounting::class);
    }
}
