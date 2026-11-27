<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient\Tests\Feature;

use Carbon\CarbonImmutable;
use Maarheeze\CompassClient\SchedulerLastRun;
use Maarheeze\CompassClient\Tests\TestCase;

use function fake;

class SchedulerLastRunTest extends TestCase
{
    public function testLastRanAtIsTheRememberedStart(): void
    {
        $startedAt = CarbonImmutable::instance(fake()->dateTime())->startOfSecond();

        $schedulerLastRun = $this->app->make(SchedulerLastRun::class);
        $schedulerLastRun->remember($startedAt);

        $this->assertEquals($startedAt, $schedulerLastRun->lastRanAt());
    }

    public function testLastRanAtIsNullWhenNothingWasRemembered(): void
    {
        $schedulerLastRun = $this->app->make(SchedulerLastRun::class);

        $this->assertNull($schedulerLastRun->lastRanAt());
    }
}
