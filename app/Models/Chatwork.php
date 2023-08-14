<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Chatwork extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_id',
        'role',
        'name',
        'chatwork_id',
        'organization_id',
        'organization_name',
        'department',
        'avatar_image_url',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notifications(): BelongsToMany
    {
        return $this->belongsToMany(Notification::class);
    }
}
