<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'phone'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_VOTER       = 'voter';
    public const ROLE_PARTICIPANT = 'participant';
    public const ROLE_ADMIN       = 'admin';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->role === self::ROLE_ADMIN) {
            return true;
        }

        return in_array(strtolower($this->email), config('auth.admin_emails', []), true);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isParticipant(): bool
    {
        return $this->role === self::ROLE_PARTICIPANT;
    }

    public function isVoter(): bool
    {
        return $this->role === self::ROLE_VOTER;
    }

    public function canVote(): bool
    {
        return in_array($this->role, [self::ROLE_VOTER, self::ROLE_PARTICIPANT, self::ROLE_ADMIN], true);
    }

    public function canUpload(): bool
    {
        return in_array($this->role, [self::ROLE_PARTICIPANT, self::ROLE_ADMIN], true);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function participant(): HasOne
    {
        return $this->hasOne(Participant::class);
    }
}
