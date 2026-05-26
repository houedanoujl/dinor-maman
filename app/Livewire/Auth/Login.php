<?php

namespace App\Livewire\Auth;

use App\Models\Participant;
use App\Models\SmsLog;
use App\Models\User;
use App\Notifications\PasswordReset;
use App\Services\SmsDispatcher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
class Login extends Component
{
    #[Validate('required|string|max:32')]
    public string $phone = '';

    #[Validate('required|string|min:1')]
    public string $password = '';

    public bool $remember = true;

    public ?string $resendStatus = null;
    public ?string $resendError = null;

    public function resendPassword(): void
    {
        $this->resendStatus = null;
        $this->resendError = null;

        if (! User::isValidCiPhone($this->phone)) {
            $this->resendError = 'Saisissez d\'abord un numéro de téléphone valide (10 chiffres).';
            return;
        }

        $normalized = User::normalizePhone($this->phone);

        // Rate limit: 1 reinitialisation / 24h par numero (et IP).
        $key = 'resend-pwd:' . $normalized;
        $ipKey = 'resend-pwd-ip:' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);
            $hours = (int) ceil($seconds / 3600);
            $this->resendError = "Une réinitialisation a déjà été demandée pour ce numéro. Réessayez dans {$hours} h.";
            return;
        }

        if (RateLimiter::tooManyAttempts($ipKey, 5)) {
            $seconds = RateLimiter::availableIn($ipKey);
            $this->resendError = "Trop de demandes depuis cette connexion. Réessayez dans " . (int) ceil($seconds / 60) . ' min.';
            return;
        }

        $user = User::where('phone', $normalized)->first();

        // Anti-énumération: réponse identique que le numéro existe ou non.
        $this->resendStatus = 'Si ce numéro est inscrit, un nouveau mot de passe vient d\'être envoyé par SMS (et par email si disponible). Limite: 1 réinitialisation par 24 h.';

        if (! $user || $user->isAdmin()) {
            RateLimiter::hit($ipKey, 3600);
            return;
        }

        // Verrou 24h consomme uniquement quand un compte existe pour eviter rate-limit aveugle.
        RateLimiter::hit($key, 86400);
        RateLimiter::hit($ipKey, 3600);

        $password = str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        $user->forceFill([
            'password' => Hash::make($password),
            'plain_password' => $password,
        ])->save();

        $loginUrl = route('login');
        $message = "DINOR. Nouveau mot de passe: {$password}. Connectez-vous avec votre numero {$user->phone} sur {$loginUrl}.";

        app(SmsDispatcher::class)->sendNow($user->phone, SmsLog::TYPE_CREDENTIALS, $message);

        $email = $user->email ?: optional(Participant::where('user_id', $user->id)->first())->email;
        if (filled($email)) {
            try {
                Notification::route('mail', $email)->notify(new PasswordReset($password, $user->phone));
            } catch (\Throwable $e) {
                // Echec mail silencieux cote utilisateur — anti-enumeration.
            }
        }
    }

    public function submit()
    {
        $this->validate();

        if (! User::isValidCiPhone($this->phone)) {
            throw ValidationException::withMessages([
                'phone' => 'Numéro invalide. 10 chiffres requis (ex: 07 08 09 10 11).',
            ]);
        }

        $normalized = User::normalizePhone($this->phone);

        $key = 'login:' . $normalized . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'phone' => "Trop de tentatives. Réessayez dans {$seconds} secondes.",
            ]);
        }

        if (! Auth::attempt(['phone' => $normalized, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($key, 300);
            throw ValidationException::withMessages([
                'phone' => 'Téléphone ou mot de passe incorrect.',
            ]);
        }

        RateLimiter::clear($key);
        request()->session()->regenerate();

        $user = Auth::user();
        $user->forceFill([
            'last_login_ip' => request()->ip(),
            'last_login_at' => now(),
        ])->saveQuietly();

        if (session()->has('url.intended')) {
            return redirect()->intended();
        }

        if ($user->isAdmin()) {
            return redirect('/admin');
        }

        return redirect()->route('contest.gallery')
            ->with('status', 'Connexion réussie.');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
