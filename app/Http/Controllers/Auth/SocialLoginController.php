<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    protected array $allowed = ['google', 'facebook'];

    public function redirect(string $provider)
    {
        if (! in_array($provider, $this->allowed, true)) {
            abort(404);
        }

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        if (! in_array($provider, $this->allowed, true)) {
            abort(404);
        }

        try {
            // Use stateful flow by default; if you need stateless (API) use ->stateless()->user()
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['oauth' => 'Unable to login using ' . $provider . '. Please try again.']);
        }

        $providerId = $socialUser->getId();
        $email = $socialUser->getEmail();
        $name = $socialUser->getName() ?? $socialUser->getNickname() ?? 'User';

        // 1) Try to find by provider + provider_id
        $user = User::where('provider', $provider)
                    ->where('provider_id', $providerId)
                    ->first();

        // 2) If not found, try by email to link accounts
        if (! $user && $email) {
            $user = User::where('email', $email)->first();
        }

        // 3) Create user if still not found
        if (! $user) {
            $user = User::create([
                'name' => $name,
                'email' => $email ?? "{$provider}_{$providerId}@example.com",
                'password' => Hash::make(Str::random(24)),
                'provider' => $provider,
                'provider_id' => $providerId,
                // optionally store token if you added provider_token column
                // 'provider_token' => $socialUser->token ?? null,
            ]);
        } else {
            // Update provider info if missing or changed
            $user->update([
                'provider' => $provider,
                'provider_id' => $providerId,
                // 'provider_token' => $socialUser->token ?? $user->provider_token,
            ]);
        }

        Auth::login($user, true);

        return redirect()->intended('/dashboard');
    }
}
