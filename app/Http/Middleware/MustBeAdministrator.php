<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MustBeAdministrator
{

    public function handle(Request $request, Closure $next)
    {

        if (optional(auth()->user())->username !== 'sebastin25') {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
