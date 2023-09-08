<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MacroTemplate extends Model
{
    use HasFactory;

    public const UNREGISTRABLE_STATUS = 0;
    public const REGISTRABLE_STATUS = 1;
    public const STATES = [
        self::UNREGISTRABLE_STATUS => 'Unregistrable',
        self::REGISTRABLE_STATUS => 'Registrable',
    ];

    protected $fillable = [
        'macro_configuration_id', 'type', 'payload', 'status',
    ];

    public function getPayloadDecodeAttribute(): array
    {
        return json_decode($this->payload, true);
    }

    public function macroConfiguration(): BelongsTo
    {
        return $this->belongsTo(MacroConfiguration::class);
    }
}
