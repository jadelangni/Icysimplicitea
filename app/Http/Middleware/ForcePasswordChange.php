<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Routes that should be accessible even when password change is required
     */
    protected array $except = [
        'password.change',
        'password.change.update',
        'logout',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if user must change password
            if ($user->must_change_password) {
                // Allow access to password change and logout routes
                $currentRoute = $request->route()?->getName();
                
                if (!in_array($currentRoute, $this->except)) {
                    return redirect()->route('password.change')
                        ->with('warning', 'You must change your password before continuing.');
                }
            }
        }

        return $next($request);
    }
}
