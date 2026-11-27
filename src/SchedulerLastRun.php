<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

use function is_string;

readonly class SchedulerLastRun
{
    private const string CACHE_KEY = 'compass-client.scheduler_last_ran_at';

    public function __construct(
        private CacheRepository $cache,
    ) {
    }

    public function lastRanAt(): ?CarbonImmutable
    {
        $lastRanAt = $this->cache->get(self::CACHE_KEY);

        if (!is_string($lastRanAt)) {
            return null;
        }

        return CarbonImmutable::parse($lastRanAt);
    }

    public function remember(CarbonImmutable $startedAt): void
    {
        $this->cache->forever(self::CACHE_KEY, $startedAt->toIso8601String());
    }
}
