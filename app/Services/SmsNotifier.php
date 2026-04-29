<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsNotifier
{
    public function send(string $phone, string $message): void
    {
        $provider = (string) config('services.sms.provider', 'log');

        if ($provider === 'log') {
            Log::info('SMS notification', [
                'phone' => $phone,
                'message' => $message,
            ]);

            return;
        }

        $apiUrl = (string) config('services.sms.api_url');
        $apiKey = (string) config('services.sms.api_key');
        $from = (string) config('services.sms.from');

        if (! $apiUrl || ! $apiKey) {
            Log::warning('SMS provider configured without API credentials.', [
                'provider' => $provider,
            ]);

            return;
        }

        try {
            Http::timeout(8)
                ->withToken($apiKey)
                ->post($apiUrl, [
                    'to' => $phone,
                    'from' => $from,
                    'message' => $message,
                ])
                ->throw();
        } catch (\Throwable $e) {
            Log::error('SMS send failed', [
                'phone' => $phone,
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
