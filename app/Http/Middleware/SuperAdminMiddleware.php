<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;

class SuperAdminMiddleware
{
    protected $auth;

    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    public function handle(Request $request, Closure $next)
    {
        if ($this->auth->getUser()->name == "charles" && $this->auth->getUser()->role == "admin") {
            
        } else {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
