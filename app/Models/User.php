<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'is_admin',
        'profile_photo_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Check the user is super admin.
     */
    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    /**
     * Generate signature for verification.
     */
    public function getSignatureVerifyEmail($token, $expires): string
    {
        return sha1($this->getKey().$token.$expires.config('app.key'));
    }

    /**
     * Get full profile photo url of the user.
     */
    public function getProfilePhotoAttribute(): string
    {
        if ($this->profile_photo_path) {
            return Storage::url($this->profile_photo_path);
        }

        return config('filesystems.profile_photo_default', '').$this->name;
    }

    public function bookmarks(?string $bookmarkableType = null): HasMany
    {
        return $this->hasMany(Bookmark::class)
            ->when($bookmarkableType, function ($query, $bookmarkableType) {
                $query->where('bookmarkable_type', $bookmarkableType);
            });
    }

    public function hasBookmarked(Model $model)
    {
        return ($this->relationLoaded('bookmarks') ? $this->bookmarks : $this->bookmarks())
            ->where('bookmarkable_id', $model->getKey())
            ->where('bookmarkable_type', $model->getMorphClass())
            ->exists();
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(UserSetting::class);
    }

    public function chatwork(): HasOne
    {
        return $this->hasOne(Chatwork::class);
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class);
    }

    public function withAllRels(): static
    {
        return $this->where('id', $this->getKey())->with(['chatwork', 'company', 'teams', 'roles'])->first();
    }
}
