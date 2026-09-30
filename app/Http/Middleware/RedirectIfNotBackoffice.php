<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNotBackoffice
{
    /** Guards the whole /admin area; customers never get past this. */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('admin.login');
        }

        abort_unless($user->canAccessBackoffice(), 403);

        return $next($request);
    }
}
