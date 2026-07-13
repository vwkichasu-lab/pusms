<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LogoutInactiveUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $timeoutMinutes = (int) config('session.inactivity_timeout', 30);

        if ($timeoutMinutes <= 0) {
            return $next($request);
        }

        $lastActivity = (int) $request->session()->get('last_activity_at', time());
        $timeoutSeconds = $timeoutMinutes * 60;

        if ((time() - $lastActivity) > $timeoutSeconds) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/admin/login')
                ->with('status', 'You were signed out because your session was inactive.');
        }

        $request->session()->put('last_activity_at', time());

        return $next($request);
    }
}
