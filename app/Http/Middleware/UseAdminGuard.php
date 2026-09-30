<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Makes every admin route resolve auth() against the "admin" guard instead
 * of "web", for the rest of this request. Both guards are session-based but
 * Laravel stores each guard's logged-in user under its own key within the
 * same session — so being authenticated on "admin" and "web" at once, in the
 * same browser, doesn't overwrite either one. That's what lets someone stay
 * signed in to the admin panel in one tab while signed in as a completely
 * different account on the storefront in another.
 */
class UseAdminGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        Auth::shouldUse('admin');

        return $next($request);
    }
}
