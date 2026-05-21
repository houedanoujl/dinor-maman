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
        try {
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
        } catch (\Throwable $e) {
            Log::error('SmsLog lookup failed (table missing ?)', [
                'phone' => $phone,
                'type'  => $type,
                'error' => $e->getMessage(),
            ]);
            // Continue : on tente quand même l'envoi sans idempotence.
        }

        try {
            $this->twilio->send($phone, $message);
        } catch (\Throwable $e) {
            Log::error('SMS échec', [
                'phone' => $phone,
                'type'  => $type,
                'error' => $e->getMessage(),
            ]);

            try {
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
            } catch (\Throwable $ignored) {
                Log::warning('SmsLog write failed', ['error' => $ignored->getMessage()]);
            }

            return false;
        }

        try {
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
        } catch (\Throwable $ignored) {
            Log::warning('SmsLog write failed (sent)', ['error' => $ignored->getMessage()]);
        }

        return true;
    }

    public function hasSent(string $phone, string $type): bool
    {
        return SmsLog::where('phone', $phone)
            ->where('type', $type)
            ->where('status', SmsLog::STATUS_SENT)
            ->exists();
    }
}
