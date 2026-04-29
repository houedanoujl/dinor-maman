<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_id',
        'ref_participant_id',
        'session_hash',
        'ip_address',
        'user_agent',
        'visited_at',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function refParticipant(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'ref_participant_id');
    }
}
