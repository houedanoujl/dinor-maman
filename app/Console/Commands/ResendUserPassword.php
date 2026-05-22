<?php

namespace App\Console\Commands;

use App\Models\SmsLog;
use App\Models\User;
use App\Services\SmsDispatcher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResendUserPassword extends Command
{
    protected $signature = 'user:resend-password {phone : Numero (10 chiffres CI ou format E.164)}';

    protected $description = 'Régénère et renvoie un mot de passe par SMS pour un utilisateur (votant/participant).';

    public function handle(SmsDispatcher $sms): int
    {
        $raw = (string) $this->argument('phone');

        if (! User::isValidCiPhone($raw)) {
            $this->error("Numéro invalide: {$raw}");
            return self::FAILURE;
        }

        $normalized = User::normalizePhone($raw);
        $user = User::where('phone', $normalized)->first();

        if (! $user) {
            $this->error("Aucun utilisateur trouvé pour {$normalized}.");
            return self::FAILURE;
        }

        if ($user->isAdmin()) {
            $this->error('Refus: ce compte est administrateur. Réinitialisez via la base ou /admin.');
            return self::FAILURE;
        }

        $password = str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        $user->forceFill([
            'password' => Hash::make($password),
            'plain_password' => $password,
        ])->save();

        $loginUrl = route('login');
        $message = "DINOR. Nouveau mot de passe: {$password}. Connectez-vous avec votre numero {$user->phone} sur {$loginUrl}.";

        [$ok, $err] = $sms->sendNow($user->phone, SmsLog::TYPE_CREDENTIALS, $message);

        if (! $ok) {
            $this->error("Échec envoi SMS: {$err}");
            $this->warn("Mot de passe régénéré: {$password} (communiquer manuellement).");
            return self::FAILURE;
        }

        $this->info("SMS envoyé à {$user->phone}.");
        $this->line("Nouveau mot de passe: {$password}");

        return self::SUCCESS;
    }
}
