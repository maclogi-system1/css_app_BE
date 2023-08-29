<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MacroGraph extends Model
{
    use HasFactory;

    protected $fillable = [
        'macro_configuration_id', 'title', 'axis_x', 'axis_y', 'graph_type', 'position_display',
    ];

    public function macroConfiguration(): BelongsTo
    {
        return $this->belongsTo(MacroConfiguration::class);
    }
}
