<?php

namespace App\Observers;

use App\Models\Participant;
use App\Models\SmsLog;
use App\Services\SmsDispatcher;

class ParticipantObserver
{
    public function __construct(private SmsDispatcher $sms) {}

    public function updated(Participant $participant): void
    {
        if (! $participant->wasChanged('status')) {
            return;
        }

        $phone = $participant->phone ?: optional($participant->user)->phone;
        if (! $phone) {
            return;
        }

        $status = $participant->status;

        if ($status === Participant::STATUS_APPROVED) {
            $url = route('contest.gallery');
            $msg = "DINOR : votre photo a ete approuvee ! Elle est en ligne dans la galerie : {$url}. Partagez-la pour recevoir des votes.";
            $this->sms->sendOnce($phone, SmsLog::TYPE_APPROVAL, $msg);
            return;
        }

        if ($status === Participant::STATUS_REJECTED) {
            $reason = trim((string) $participant->rejection_reason);
            $reasonPart = $reason !== '' ? " Motif : {$reason}." : '';
            $msg = "DINOR : votre photo n'a pas ete retenue.{$reasonPart} Vous pouvez en soumettre une nouvelle depuis votre espace.";
            $this->sms->sendOnce($phone, SmsLog::TYPE_REJECTION, $msg);
            return;
        }
    }
}
