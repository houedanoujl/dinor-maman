<?php

namespace App\Filament\Resources\ParticipantResource\Pages;

use App\Filament\Resources\ParticipantResource;
use App\Models\Participant;
use App\Models\SmsLog;
use App\Models\User;
use App\Notifications\PasswordReset;
use App\Services\SmsDispatcher;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

class CreateParticipant extends CreateRecord
{
    protected static string $resource = ParticipantResource::class;

    protected ?string $generatedPassword = null;
    protected ?User $linkedUser = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Normalise phone CI vers +225XXXXXXXXXX
        if (! empty($data['phone'])) {
            if (User::isValidCiPhone($data['phone'])) {
                $data['phone'] = User::normalizePhone($data['phone']);
            }
        }

        // Cherche User existant via phone, sinon crée.
        $user = null;
        if (! empty($data['phone'])) {
            $user = User::where('phone', $data['phone'])->first();
        }

        if (! $user) {
            $password = str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
            $user = User::create([
                'name'           => trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')),
                'phone'          => $data['phone'] ?? null,
                'email'          => $data['email'] ?? null,
                'password'       => Hash::make($password),
                'plain_password' => $password,
                'role'           => User::ROLE_PARTICIPANT,
                'signup_ip'      => request()->ip(),
            ]);
            $this->generatedPassword = $password;
        } else {
            // Update email si fourni et user n'en a pas
            if (! empty($data['email']) && empty($user->email)) {
                $user->forceFill(['email' => $data['email']])->save();
            }
        }

        $this->linkedUser = $user;
        $data['user_id'] = $user->id;
        $data['phone_verified_at'] = $data['phone_verified_at'] ?? now();

        return $data;
    }

    protected function afterCreate(): void
    {
        if (! $this->generatedPassword || ! $this->linkedUser) {
            return;
        }

        $user = $this->linkedUser;
        $password = $this->generatedPassword;
        $loginUrl = route('login');
        $smsMsg = "DINOR. Votre compte a ete cree. Mot de passe: {$password}. Connectez-vous sur {$loginUrl} avec votre numero {$user->phone}.";

        $channels = [];

        if (filled($user->phone)) {
            [$ok] = app(SmsDispatcher::class)->sendNow($user->phone, SmsLog::TYPE_CREDENTIALS, $smsMsg);
            if ($ok) $channels[] = 'SMS';
        }

        $email = $this->record->email ?: $user->email;
        if (filled($email)) {
            try {
                Notification::route('mail', $email)->notify(new PasswordReset($password, $user->phone ?: ''));
                $channels[] = 'Email';
            } catch (\Throwable $e) {
                // Silent
            }
        }

        FilamentNotification::make()
            ->success()
            ->title('Compte créé')
            ->body(empty($channels)
                ? "Mot de passe généré: {$password} (à communiquer manuellement)."
                : 'Identifiants envoyés via ' . implode(' + ', $channels) . '.')
            ->persistent()
            ->send();
    }
}
