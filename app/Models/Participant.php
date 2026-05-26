<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Participant extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'city',
        'photo_path',
        'anecdote',
        'status',
        'email',
        'approved_at',
        'rejection_reason',
        'dashboard_token',
        'sms_code',
        'sms_code_expires_at',
        'phone_verified_at',
    ];

    /**
     * Plaintext dashboard token, set after creation/regeneration.
     * Not persisted; only available in the request that generated it.
     */
    public ?string $plainDashboardToken = null;

    protected static function booted(): void
    {
        static::creating(function (self $participant) {
            if (empty($participant->dashboard_token)) {
                $plain = Str::random(40);
                $participant->plainDashboardToken = $plain;
                $participant->dashboard_token = hash('sha256', $plain);
            }
        });
    }

    public function regenerateDashboardToken(): string
    {
        $plain = Str::random(40);
        $this->plainDashboardToken = $plain;
        $this->dashboard_token = hash('sha256', $plain);
        $this->save();
        return $plain;
    }

    public static function findByDashboardToken(string $plain): ?self
    {
        return static::where('dashboard_token', hash('sha256', $plain))->first();
    }

    protected $casts = [
        'approved_at'          => 'datetime',
        'sms_code_expires_at'  => 'datetime',
        'phone_verified_at'    => 'datetime',
        'vote_count'           => 'integer',
    ];

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png'])
            ->useDisk('public');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(400)->height(400)->sharpen(8)->nonQueued();

        $this->addMediaConversion('card')
            ->width(800)->height(800)->nonQueued();
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getDashboardUrlAttribute(): ?string
    {
        // URL ne peut être générée qu'à partir du plaintext (présent uniquement
        // dans la requête qui a créé/régénéré le token).
        return $this->plainDashboardToken
            ? route('participant.dashboard', $this->plainDashboardToken)
            : null;
    }
}
