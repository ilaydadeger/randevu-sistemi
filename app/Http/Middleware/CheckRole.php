<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check() || auth()->user()->role !== $role) {
            // Redirect or abort if the user does not have the required role
            return redirect('/')->withErrors(['error' => 'Bu sayfaya erişim yetkiniz yok.']);
        }

        return $next($request);
    }
}
