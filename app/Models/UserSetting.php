<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    use HasFactory;

    public const MACRO_DISPLAY_MACROGRAPH_1 = 'macro_display_macrograph_1';
    public const MACRO_DISPLAY_MACROGRAPH_2 = 'macro_display_macrograph_2';
    public const MACRO_DISPLAY_MACROGRAPH_3 = 'macro_display_macrograph_3';
    public const MACRO_DISPLAY_MACROGRAPH_4 = 'macro_display_macrograph_4';

    public const VALIDATION_RULES = [
        self::MACRO_DISPLAY_MACROGRAPH_1 => ['nullable', 'in:1,0'],
        self::MACRO_DISPLAY_MACROGRAPH_2 => ['nullable', 'in:1,0'],
        self::MACRO_DISPLAY_MACROGRAPH_3 => ['nullable', 'in:1,0'],
        self::MACRO_DISPLAY_MACROGRAPH_4 => ['nullable', 'in:1,0'],
    ];

    public const DEFAULT_SETTINGS = [
        self::MACRO_DISPLAY_MACROGRAPH_1 => 1,
        self::MACRO_DISPLAY_MACROGRAPH_2 => 1,
        self::MACRO_DISPLAY_MACROGRAPH_3 => 1,
        self::MACRO_DISPLAY_MACROGRAPH_4 => 1,
    ];

    protected $fillable = [
        'user_id', 'key', 'value', 'created_at', 'updated_at',
    ];
}
