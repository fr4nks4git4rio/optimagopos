<?php

namespace App\Http\Livewire\Auth\Passwords;

use App\Jobs\SendEmailJob;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Livewire\Component;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as RulesPassword;

class ResetPassword extends Component
{
    public $token;
    public $email;
    public $password;
    public $password_confirmation;

    public function mount($token)
    {
        $this->token = $token;
        if (request()->email)
            $this->email = request()->email;
    }

    // protected $messages = [
    //     'code.required' => 'Campo obligatorio.',
    //     'code.numeric' => 'Campo numérico.',
    //     'code.digits' => 'Entre 6 dígitos.',
    // ];

    public function resetPassword()
    {
        $data = $this->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', RulesPassword::default()],
        ]);

        // Intentar restablecer la contraseña
        $status = Password::reset(
            [
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'token' => $this->token
            ],
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET)
            return redirect()->route('login')->with('status', __($status));

        $this->addError('email', __($status));
    }

    public function render()
    {
        return view('livewire.auth.passwords.reset');
    }
}
