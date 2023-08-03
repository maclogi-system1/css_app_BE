<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PolicyAttachment extends Model
{
    use HasFactory, HasUuids;

    public const IMAGE_TYPE = 'image';
    public const IMAGE_PATH = 'images/policy_attachment';

    public const TEXT_TYPE = 'text';
    public const TEXT_PATH = 'texts/policy_attachment';

    protected $fillable = [
        'policy_id',
        'attachment_key',
        'name',
        'path',
        'type',
        'disk',
    ];

    public function getImageUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }
}
