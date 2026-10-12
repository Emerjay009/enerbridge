<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureApproved
{
    // Blocks logged-in users whose account is not (or no longer) approved.
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->approval_status !== 'approved') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->withErrors([
                'email' => 'Your account is not approved. Please wait for administrator approval.',
            ]);
        }

        return $next($request);
    }
}
