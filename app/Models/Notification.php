<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Notification extends Model
{
    use HasFactory;

    public const SEND_TO_NORMAL = 'normal';
    public const SEND_TO_SPECIFIED_MEMBER = 'specified_members';
    public const SEND_TO_ALL = 'all';

    protected $fillable = [
        'message', 'room_id', 'type', 'send_to',
    ];

    public function chatworks(): BelongsToMany
    {
        return $this->belongsToMany(Chatwork::class);
    }
}
