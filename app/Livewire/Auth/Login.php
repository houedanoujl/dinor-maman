<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
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
