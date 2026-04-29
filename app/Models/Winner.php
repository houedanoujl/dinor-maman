<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Winner extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_id',
        'rank',
        'vote_count_snapshot',
        'announced_at',
        'contest_cycle',
    ];

    protected $casts = [
        'announced_at' => 'datetime',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }
}
