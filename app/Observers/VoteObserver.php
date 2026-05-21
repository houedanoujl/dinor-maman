<?php

namespace App\Observers;

use App\Models\Participant;
use App\Models\Vote;

class VoteObserver
{
    public function deleted(Vote $vote): void
    {
        if (! $vote->participant_id) {
            return;
        }

        Participant::where('id', $vote->participant_id)
            ->where('vote_count', '>', 0)
            ->decrement('vote_count');
    }
}
