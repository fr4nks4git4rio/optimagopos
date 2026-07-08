<?php

namespace App\Http\Livewire\Auth;

use Livewire\Component;

class SocialLogin extends Component
{
    public function loginWithGoogle()
    {
        return redirect()->route('auth.provider-redirect', 'google');
    }

    public function render()
    {
        return view('livewire.auth.social-login');
    }
}
