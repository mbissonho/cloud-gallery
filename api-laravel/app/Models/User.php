<?php

namespace App\Models;


use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'bio',
        'photo_storage_key',
        'new_photo_storage_hash'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    public function latestImage(): HasOne
    {
        return $this->hasOne(Image::class)
            ->where('status', ImageStatus::AVAILABLE->value)
            ->latestOfMany();
    }

    public function publishedImages(): HasMany
    {
        return $this->hasMany(Image::class)
            ->where('status', ImageStatus::AVAILABLE->value);
    }

    public function getProfilePhotoKey(): ?string
    {
        return $this->photo_storage_key;
    }

    public function getProfilePhotoUrl(): ?string
    {
        return Storage::disk('thumbnail-profile-image')->url('profile/' . $this->photo_storage_key ?? null);
    }
}
