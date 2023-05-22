<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as Model;

class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'guard_name',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'pivot',
    ];

    public function hasUser($value, $field = null)
    {
        if (! is_null($field)) {
            return $this->users()->where($field, $value)->exists();
        }

        return $this->users()
            ->where('name', $value)
            ->orWhere('email', $value)
            ->orWhere('id', $value)
            ->exists();
    }
}
