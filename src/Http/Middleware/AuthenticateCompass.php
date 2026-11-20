<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maarheeze\CompassClient\SiteKey;
use Symfony\Component\HttpFoundation\Response;

use function hash_equals;

readonly class AuthenticateCompass
{
    public function __construct(
        private SiteKey $siteKey,
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
        $siteKey = $this->siteKey->configured();

        if ($siteKey === null) {
            return false;
        }

        $bearerToken = $request->bearerToken();

        if ($bearerToken === null) {
            return false;
        }

        return hash_equals($siteKey, $bearerToken);
    }
}
