<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

use function is_string;

readonly class CompassApiUrl
{
    private const string CACHE_KEY = 'compass-client.compass_api_url';

    public function __construct(
        private CacheRepository $cache,
    ) {
    }

    public function remember(string $url, CarbonImmutable $validUntil): void
    {
        $this->cache->put(self::CACHE_KEY, $url, $validUntil);
    }

    public function url(): ?string
    {
        $url = $this->cache->get(self::CACHE_KEY);

        if (!is_string($url)) {
            return null;
        }

        return $url;
    }
}
