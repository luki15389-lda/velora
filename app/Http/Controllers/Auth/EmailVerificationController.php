<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\Cart;
use App\Models\User;
use App\Models\Wishlist;
use App\Services\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class EmailVerificationController extends Controller
{
    private const SESSION_KEY = 'pending_registration';

    public function notice(Request $request): View|RedirectResponse
    {
        $pending = $request->session()->get(self::SESSION_KEY);

        if (! $pending) {
            return redirect()->route('register');
        }

        return view('auth.verify-email', ['email' => $pending['email']]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $pending = $request->session()->get(self::SESSION_KEY);

        if (! $pending) {
            return redirect()->route('register');
        }

        $request->validate(['code' => ['required', 'string', 'size:6']]);

        $key = 'otp-verify|'.$pending['email'];

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'code' => 'Too many attempts. Request a new code and try again in a minute.',
            ]);
        }

        $valid = $pending['expires_at'] >= now()->timestamp
            && hash_equals($pending['code'], hash('sha256', (string) $request->string('code')));

        if (! $valid) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'code' => 'That code is wrong or has expired.',
            ]);
        }

        RateLimiter::clear($key);

        // Someone may have taken the email while this code was pending.
        if (User::where('email', $pending['email'])->exists()) {
            $request->session()->forget(self::SESSION_KEY);

            return redirect()->route('login')->with('status', 'That email is already registered. Sign in instead.');
        }

        // The account is created only now, after the code is confirmed.
        $user = DB::transaction(function () use ($pending) {
            $user = new User([
                'name' => $pending['name'],
                'email' => $pending['email'],
                'phone' => $pending['phone'],
                'password' => $pending['password'], // already hashed
            ]);
            $user->role = Role::Customer;
            $user->email_verified_at = now();
            $user->save();

            Cart::create(['user_id' => $user->id]);
            Wishlist::create(['user_id' => $user->id, 'name' => 'My wishlist']);

            return $user;
        });

        $request->session()->forget(self::SESSION_KEY);

        Auth::login($user);
        $request->session()->regenerate();

        app(CartService::class)->mergeGuestCartInto($user);

        return redirect()->route('home')->with('status', 'Email verified. Welcome to VELORA.');
    }

    public function resend(Request $request): RedirectResponse
    {
        $pending = $request->session()->get(self::SESSION_KEY);

        if (! $pending) {
            return redirect()->route('register');
        }

        $key = 'otp-resend|'.$pending['email'];

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->with('error', "Wait {$seconds} seconds before requesting another code.");
        }

        RateLimiter::hit($key, 60);

        $code = (string) random_int(100000, 999999);

        try {
            Mail::to($pending['email'])->send(new OtpMail(new User(['name' => $pending['name']]), $code));
        } catch (TransportExceptionInterface $e) {
            report($e);

            return back()->with('error', 'We could not send the email right now. Please try again in a minute.');
        }

        $request->session()->put(self::SESSION_KEY, [
            ...$pending,
            'code' => hash('sha256', $code),
            'expires_at' => now()->addMinutes(10)->timestamp,
        ]);

        return back()->with('status', 'A new code is on its way.');
    }
}
