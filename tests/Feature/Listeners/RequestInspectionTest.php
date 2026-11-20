<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient\Tests\Feature\Listeners;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Events\MaintenanceModeEnabled;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request;
use Maarheeze\CompassClient\CompassApiUrl;
use Maarheeze\CompassClient\Listeners\RequestInspection;
use Maarheeze\CompassClient\Tests\TestCase;

use function fake;
use function sprintf;

class RequestInspectionTest extends TestCase
{
    public function testHandleAsksCompassToInspectTheSite(): void
    {
        $key = fake()->sha1();
        $url = fake()->url();
        $http = $this->app->make(HttpFactory::class);
        $http->fake();
        $compassApiUrl = $this->app->make(CompassApiUrl::class);
        $compassApiUrl->remember($url, CarbonImmutable::instance(fake()->dateTimeBetween('+1 hour', '+1 day')));
        $this->setConfiguration('compass-client.key', $key);

        $requestInspection = $this->app->make(RequestInspection::class);
        $requestInspection->handle(new MaintenanceModeEnabled());

        $http->assertSent(static function (Request $request) use ($key, $url): bool {
            return $request->method() === 'POST'
                && $request->url() === $url
                && $request->hasHeader('Authorization', sprintf('Bearer %s', $key));
        });
    }

    public function testHandleSendsNothingWithoutARememberedUrl(): void
    {
        $http = $this->app->make(HttpFactory::class);
        $http->fake();
        $this->setConfiguration('compass-client.key', fake()->sha1());

        $requestInspection = $this->app->make(RequestInspection::class);
        $requestInspection->handle(new MaintenanceModeEnabled());

        $http->assertNothingSent();
    }

    public function testHandleSendsNothingWithoutAKey(): void
    {
        $http = $this->app->make(HttpFactory::class);
        $http->fake();
        $compassApiUrl = $this->app->make(CompassApiUrl::class);
        $validUntil = CarbonImmutable::instance(fake()->dateTimeBetween('+1 hour', '+1 day'));
        $compassApiUrl->remember(fake()->url(), $validUntil);
        $this->setConfiguration('compass-client.key', null);

        $requestInspection = $this->app->make(RequestInspection::class);
        $requestInspection->handle(new MaintenanceModeEnabled());

        $http->assertNothingSent();
    }

    public function testHandleIgnoresACompassThatCannotBeReached(): void
    {
        $http = $this->app->make(HttpFactory::class);
        $http->fake(HttpFactory::failedConnection());
        $compassApiUrl = $this->app->make(CompassApiUrl::class);
        $validUntil = CarbonImmutable::instance(fake()->dateTimeBetween('+1 hour', '+1 day'));
        $compassApiUrl->remember(fake()->url(), $validUntil);
        $this->setConfiguration('compass-client.key', fake()->sha1());

        $requestInspection = $this->app->make(RequestInspection::class);
        $requestInspection->handle(new MaintenanceModeEnabled());

        $http->assertSentCount(1);
    }
}
