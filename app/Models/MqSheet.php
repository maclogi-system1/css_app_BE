<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MqSheet extends Model
{
    use HasFactory, HasUuids;

    public const DEFAULT_NAME = 'AI売上予測MQ会計';

    protected $fillable = [
        'id', 'store_id', 'name', 'is_default', 'created_at', 'updated_at',
    ];

    public function isDefault(): bool
    {
        return $this->is_default;
    }

    public function mqAccountings(): HasMany
    {
        return $this->hasMany(MqAccounting::class);
    }
}
