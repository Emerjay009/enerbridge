<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;

class EnsureAdministrator
{
    public function handle(Request $request, Closure $next)
    {
        $slug = Role::find($request->user()?->role_id)?->slug;

        abort_unless($slug === 'administrator', 403, 'Only administrators can open this page.');

        return $next($request);
    }
}
