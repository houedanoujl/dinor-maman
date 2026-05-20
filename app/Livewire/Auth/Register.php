<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
class Register extends Component
{
    #[Validate('required|in:voter,participant')]
    public string $role = User::ROLE_VOTER;

    #[Validate('required|string|min:2|max:100')]
    public string $name = '';

    #[Validate('required|email|max:150|unique:users,email')]
    public string $email = '';

    #[Validate('required|string|min:8|max:100|confirmed')]
    public string $password = '';

    public string $password_confirmation = '';

    #[Validate('accepted')]
    public bool $consent = false;

    public function submit()
    {
        $this->validate();

        $user = User::create([
            'name'     => trim($this->name),
            'email'    => strtolower(trim($this->email)),
            'password' => Hash::make($this->password),
            'role'     => $this->role,
        ]);

        Auth::login($user, true);

        // Si Participant → redirige vers formulaire concours pour uploader sa photo
        if ($user->role === User::ROLE_PARTICIPANT) {
            return redirect()->route('contest.form')
                ->with('status', 'Bienvenue ! Complétez votre participation ci-dessous.');
        }

        // Sinon Votant → galerie
        return redirect()->route('contest.gallery')
            ->with('status', 'Bienvenue ! Vous pouvez maintenant voter pour vos favoris.');
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
