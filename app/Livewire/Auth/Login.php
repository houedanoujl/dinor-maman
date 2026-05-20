<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
class Login extends Component
{
    #[Validate('required|email|max:150')]
    public string $email = '';

    #[Validate('required|string|min:1')]
    public string $password = '';

    public bool $remember = true;

    public function submit()
    {
        $this->validate();

        $key = 'login:' . strtolower(trim($this->email)) . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Trop de tentatives. Réessayez dans {$seconds} secondes.",
            ]);
        }

        if (! Auth::attempt(['email' => strtolower(trim($this->email)), 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($key, 300);
            throw ValidationException::withMessages([
                'email' => 'Identifiants incorrects.',
            ]);
        }

        RateLimiter::clear($key);
        request()->session()->regenerate();

        $user = Auth::user();

        // Redirige vers l'URL voulue avant login si elle existe
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
