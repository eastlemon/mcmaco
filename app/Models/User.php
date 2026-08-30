<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'phone', 'city', 'avatar', 'bio', 'is_admin'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) $this->is_admin;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_admin' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<Ad, $this> */
    public function ads(): HasMany
    {
        return $this->hasMany(Ad::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<Favorite, $this> */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<Chat, $this> */
    public function chatsAsBuyer(): HasMany
    {
        return $this->hasMany(Chat::class, 'buyer_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<Chat, $this> */
    public function chatsAsSeller(): HasMany
    {
        return $this->hasMany(Chat::class, 'seller_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<Report, $this> */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_user_id');
    }
}
