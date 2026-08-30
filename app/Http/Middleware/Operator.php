<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Operator
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            if (!in_array(Auth::user()->role, [1, 2])) {
                return redirect()->route('home')->with('wrong', 'Доступно только для сотрудников');
            }
        } else {
            return redirect()->route('home')->with('wrong', 'Доступно только для авторизированных сотрудников');
        }
        return $next($request);
    }
}
