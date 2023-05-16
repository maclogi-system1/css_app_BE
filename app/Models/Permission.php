<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as Model;

class Permission extends Model
{
    use HasFactory;

    protected $hidden = [
        'pivot',
    ];

    public const VIEW_CODE = 'view';
    public const CREATE_CODE = 'create';
    public const EDIT_CODE = 'edit';
    public const DELETE_CODE = 'delete';
    public const CODES = [
        self::VIEW_CODE,
        self::CREATE_CODE,
        self::EDIT_CODE,
        self::DELETE_CODE,
    ];
}
