<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Socialite;

class SocialiteLoginController extends Controller
{
    public function redirectToProvider($provider) {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider) {
        $user = Socialite::driver($provider)->user();
        
        $authUser = User::firstOrCreate(
             ['email' => $user->getEmail()],
             ['name' => $user->getName()]
        );

        Auth::login($authUser);

        return redirect()->route('dashboard');
    }
}
