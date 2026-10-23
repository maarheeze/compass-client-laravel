<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient\Tests\Feature\Http\Middleware;

use Maarheeze\CompassClient\Http\RouteName;
use Maarheeze\CompassClient\Tests\TestCase;

use function fake;
use function route;

class AuthenticateCompassTest extends TestCase
{
    public function testHandleLetsTheRequestWithTheKeyThrough(): void
    {
        $key = fake()->sha1();
        $this->setConfiguration('compass-client.key', $key);

        $response = $this->withToken($key)->getJson(route(RouteName::STATUS));

        $response->assertOk();
    }

    public function testHandleRefusesARequestWithoutAKey(): void
    {
        $this->setConfiguration('compass-client.key', fake()->sha1());

        $response = $this->getJson(route(RouteName::STATUS));

        $response->assertUnauthorized();
    }

    public function testHandleRefusesARequestWithAnotherKey(): void
    {
        $this->setConfiguration('compass-client.key', fake()->unique()->sha1());

        $response = $this->withToken(fake()->unique()->sha1())->getJson(route(RouteName::STATUS));

        $response->assertUnauthorized();
    }

    public function testHandleRefusesEveryRequestWhenNoKeyIsConfigured(): void
    {
        $this->setConfiguration('compass-client.key', null);

        $response = $this->withToken(fake()->sha1())->getJson(route(RouteName::STATUS));

        $response->assertUnauthorized();
    }

    public function testHandleRefusesEveryRequestWhenTheConfiguredKeyIsEmpty(): void
    {
        $this->setConfiguration('compass-client.key', '');

        $response = $this->withToken('')->getJson(route(RouteName::STATUS));

        $response->assertUnauthorized();
    }
}
