<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $name = $googleUser->getName();
        $email = $googleUser->getEmail();
        $avatar = $googleUser->getAvatar();

        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => bcrypt(uniqid())]
        );

        $token = $user->createToken('auth_token')->plainTextToken;

        return redirect()->to("http://localhost:4200/login?token=$token&name=" . urlencode($name) . "&email=$email");
    }
}
