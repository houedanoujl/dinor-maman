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

#[Fillable(['name', 'email', 'password', 'plain_password', 'role', 'phone', 'signup_ip', 'last_login_ip', 'last_login_at'])]
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
            'last_login_at'     => 'datetime',
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

    public function participant(): HasOne
    {
        return $this->hasOne(Participant::class);
    }

    /**
     * Normalise un numéro CI vers le format E.164 +225XXXXXXXXXX.
     * Accepte: 10 chiffres bruts, avec/sans espaces, +225..., 00225..., 225...
     */
    public static function normalizePhone(string $raw): string
    {
        $digits = preg_replace('/\D/', '', $raw);

        if (str_starts_with($digits, '00225')) {
            $digits = substr($digits, 5);
        } elseif (str_starts_with($digits, '225') && strlen($digits) === 13) {
            $digits = substr($digits, 3);
        }

        return '+225' . $digits;
    }

    public static function isValidCiPhone(string $raw): bool
    {
        $digits = preg_replace('/\D/', '', $raw);

        if (str_starts_with($digits, '00225')) {
            $digits = substr($digits, 5);
        } elseif (str_starts_with($digits, '225') && strlen($digits) === 13) {
            $digits = substr($digits, 3);
        }

        return strlen($digits) === 10 && ctype_digit($digits);
    }
}
