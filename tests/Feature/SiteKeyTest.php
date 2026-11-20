<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient\Tests\Feature;

use Maarheeze\CompassClient\SiteKey;
use Maarheeze\CompassClient\Tests\TestCase;

use function fake;

class SiteKeyTest extends TestCase
{
    public function testConfiguredIsTheConfiguredSiteKey(): void
    {
        $configuredSiteKey = fake()->sha1();
        $this->setConfiguration('compass-client.key', $configuredSiteKey);

        $siteKey = $this->app->make(SiteKey::class);

        $this->assertSame($configuredSiteKey, $siteKey->configured());
    }

    public function testConfiguredIsNullWhenNoSiteKeyIsConfigured(): void
    {
        $this->setConfiguration('compass-client.key', null);

        $siteKey = $this->app->make(SiteKey::class);

        $this->assertNull($siteKey->configured());
    }

    public function testConfiguredIsNullWhenTheConfiguredSiteKeyIsEmpty(): void
    {
        $this->setConfiguration('compass-client.key', '');

        $siteKey = $this->app->make(SiteKey::class);

        $this->assertNull($siteKey->configured());
    }
}
