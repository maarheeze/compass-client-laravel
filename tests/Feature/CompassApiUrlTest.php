<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient\Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Maarheeze\CompassClient\CompassApiUrl;
use Maarheeze\CompassClient\Tests\TestCase;

use function fake;

class CompassApiUrlTest extends TestCase
{
    public function testRememberKeepsTheUrlInTheCache(): void
    {
        $url = fake()->url();
        $validUntil = CarbonImmutable::instance(fake()->dateTimeBetween('+1 hour', '+1 day'));

        $compassApiUrl = $this->app->make(CompassApiUrl::class);
        $compassApiUrl->remember($url, $validUntil);

        $this->assertSame($url, $this->app->make(CacheRepository::class)->get('compass-client.compass_api_url'));
    }

    public function testRememberLeavesOutAUrlThatIsNoLongerValid(): void
    {
        $validUntil = CarbonImmutable::instance(fake()->dateTimeBetween('-1 day', '-1 hour'));

        $compassApiUrl = $this->app->make(CompassApiUrl::class);
        $compassApiUrl->remember(fake()->url(), $validUntil);

        $this->assertFalse($this->app->make(CacheRepository::class)->has('compass-client.compass_api_url'));
    }
}
