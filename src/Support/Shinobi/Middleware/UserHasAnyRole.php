<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Support\Shinobi\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

class UserHasAnyRole
{
    /**
     * @var Illuminate\Contracts\Auth\Guard
     */
    protected $auth;

    /**
     * Create a new UserHasPermission instance.
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Run the request filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $closure
     * @param  string  $role
     * @return mixed
     */
    public function handle($request, Closure $next, ...$roles)
    {
        if (! $this->auth->check()) {
            if ($request->expectsJson()) {
                return response('Forbidden.', 403);
            }

            return abort(403);
        }

        $roles = call_user_func_array('array_merge', $roles);
        $authorized = call_user_func_array([$this->auth->user(), 'hasAnyRole'], $roles);

        if (! $authorized) {
            if ($request->expectsJson()) {
                return response('Forbidden.', 403);
            }

            return abort(403);
        }

        return $next($request);
    }
}
