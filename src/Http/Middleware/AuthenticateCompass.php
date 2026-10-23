<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient\Http\Middleware;

use Closure;
use Illuminate\Config\Repository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use function hash_equals;
use function is_string;

readonly class AuthenticateCompass
{
    public function __construct(
        private Repository $config,
    ) {
    }

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->isCompass($request)) {
            return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }

    private function isCompass(Request $request): bool
    {
        $key = $this->config->get('compass-client.key');

        if (!is_string($key)) {
            return false;
        }

        if ($key === '') {
            return false;
        }

        $bearerToken = $request->bearerToken();

        if ($bearerToken === null) {
            return false;
        }

        return hash_equals($key, $bearerToken);
    }
}
