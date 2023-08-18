<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MacroConfiguration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'conditions', 'time_conditions', 'macro_type', 'created_by', 'updated_by', 'deleted_by',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id', 'created_by');
    }
}
