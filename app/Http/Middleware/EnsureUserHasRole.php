<?php

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Usage: ->middleware('role:admin')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->status !== UserStatus::Active) {
            auth()->logout();

            return redirect()->route('login')
                ->withErrors(['email' => 'This account is suspended. Contact support to reactivate it.']);
        }

        abort_unless(in_array($user->role->value, $roles, true), 403);

        return $next($request);
    }
}
