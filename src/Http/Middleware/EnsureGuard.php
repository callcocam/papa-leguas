<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\PapaLeguas\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware EnsureGuard
 *
 * NOTA: Este middleware foi simplificado. Não há mais necessidade de
 * múltiplos guards (landlord/tenant) pois a autenticação é única via Sanctum.
 * O contexto é gerenciado apenas para rotas e paths, não para guards.
 */
class EnsureGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Middleware simplificado - apenas passa a requisição adiante
        // O guard padrão (sanctum) é usado para todos os contextos
        return $next($request);
    }
}
