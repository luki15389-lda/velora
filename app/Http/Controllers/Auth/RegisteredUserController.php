<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Does NOT create the account. The details are held in the session until the
     * emailed code is entered — the user row is only written by
     * EmailVerificationController::verify(). No code entered = no account.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'regex:/^(?:\+?62|0)8[0-9]{7,11}$/'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'phone.regex' => 'Enter a valid Indonesian mobile number — start with 08 (e.g. 081234567890) or 62 (e.g. 6281234567890), not a mix of both.',
        ]);

        if (! empty($data['phone'])) {
            // One canonical shape (62-prefixed digits), whichever format was typed.
            $data['phone'] = normalize_id_phone($data['phone']);
        }

        $code = (string) random_int(100000, 999999);

        try {
            Mail::to($data['email'])->send(new OtpMail(new User(['name' => $data['name']]), $code));
        } catch (TransportExceptionInterface $e) {
            report($e);

            // Nothing was saved, so the person can simply try again.
            return back()->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => 'We could not send the verification email right now. Please try again in a minute.']);
        }

        $request->session()->put('pending_registration', [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'code' => hash('sha256', $code),
            'expires_at' => now()->addMinutes(10)->timestamp,
        ]);

        return redirect()->route('verification.notice');
    }
}
