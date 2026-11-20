<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient\Tests\Feature;

use Carbon\CarbonImmutable;
use Maarheeze\CompassClient\CompassApiUrl;
use Maarheeze\CompassClient\Tests\TestCase;

use function fake;

class CompassApiUrlTest extends TestCase
{
    public function testUrlIsTheRememberedUrl(): void
    {
        $url = fake()->url();
        $validUntil = CarbonImmutable::instance(fake()->dateTimeBetween('+1 hour', '+1 day'));

        $compassApiUrl = $this->app->make(CompassApiUrl::class);
        $compassApiUrl->remember($url, $validUntil);

        $this->assertSame($url, $compassApiUrl->url());
    }

    public function testUrlIsNullWhenNothingWasRemembered(): void
    {
        $compassApiUrl = $this->app->make(CompassApiUrl::class);

        $this->assertNull($compassApiUrl->url());
    }

    public function testUrlIsNullWhenTheRememberedUrlIsNoLongerValid(): void
    {
        $validUntil = CarbonImmutable::instance(fake()->dateTimeBetween('-1 day', '-1 hour'));

        $compassApiUrl = $this->app->make(CompassApiUrl::class);
        $compassApiUrl->remember(fake()->url(), $validUntil);

        $this->assertNull($compassApiUrl->url());
    }
}
