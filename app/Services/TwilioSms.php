<?php

namespace App\Services;

use Twilio\Rest\Client;

class TwilioSms
{
    public function send(string $to, string $message): void
    {
        $client = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );

        $client->messages->create($to, [
            'from' => config('services.twilio.from'),
            'body' => $message,
        ]);
    }
}
