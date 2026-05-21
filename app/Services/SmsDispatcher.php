<?php

namespace App\Services;

use App\Models\SmsLog;
use Illuminate\Support\Facades\Log;

class SmsDispatcher
{
    public function __construct(private TwilioSms $twilio) {}

    /**
     * Envoie un SMS unique par (phone, type). Si déjà envoyé avec succès,
     * l'appel est ignoré (idempotent). Empêche toute demande répétée.
     */
    public function sendOnce(string $phone, string $type, string $message): bool
    {
        $existing = SmsLog::where('phone', $phone)
            ->where('type', $type)
            ->where('status', SmsLog::STATUS_SENT)
            ->first();

        if ($existing) {
            Log::info('SMS skip: déjà envoyé', [
                'phone' => $phone,
                'type'  => $type,
                'sent_at' => $existing->sent_at?->toDateTimeString(),
            ]);
            return false;
        }

        try {
            $this->twilio->send($phone, $message);

            SmsLog::updateOrCreate(
                ['phone' => $phone, 'type' => $type],
                [
                    'provider' => 'twilio',
                    'status'   => SmsLog::STATUS_SENT,
                    'message'  => $message,
                    'error'    => null,
                    'sent_at'  => now(),
                ]
            );

            return true;
        } catch (\Throwable $e) {
            Log::error('SMS échec', [
                'phone' => $phone,
                'type'  => $type,
                'error' => $e->getMessage(),
            ]);

            SmsLog::updateOrCreate(
                ['phone' => $phone, 'type' => $type],
                [
                    'provider' => 'twilio',
                    'status'   => SmsLog::STATUS_FAILED,
                    'message'  => $message,
                    'error'    => $e->getMessage(),
                    'sent_at'  => null,
                ]
            );

            return false;
        }
    }

    public function hasSent(string $phone, string $type): bool
    {
        return SmsLog::where('phone', $phone)
            ->where('type', $type)
            ->where('status', SmsLog::STATUS_SENT)
            ->exists();
    }
}
