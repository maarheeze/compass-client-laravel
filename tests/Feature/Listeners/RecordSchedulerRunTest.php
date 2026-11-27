<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient\Tests\Feature\Listeners;

use Carbon\CarbonImmutable;
use Illuminate\Console\Events\CommandStarting;
use Maarheeze\CompassClient\Listeners\RecordSchedulerRun;
use Maarheeze\CompassClient\SchedulerLastRun;
use Maarheeze\CompassClient\Tests\TestCase;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

use function fake;

class RecordSchedulerRunTest extends TestCase
{
    public function testHandleRemembersWhenTheScheduleRunStarted(): void
    {
        $now = CarbonImmutable::instance(fake()->dateTime())->startOfSecond();
        $this->app->instance(ClockInterface::class, new MockClock($now));

        $recordSchedulerRun = $this->app->make(RecordSchedulerRun::class);
        $recordSchedulerRun->handle(new CommandStarting('schedule:run', new ArrayInput([]), new NullOutput()));

        $schedulerLastRun = $this->app->make(SchedulerLastRun::class);
        $this->assertEquals($now, $schedulerLastRun->lastRanAt());
    }

    public function testHandleIgnoresAnotherCommand(): void
    {
        $recordSchedulerRun = $this->app->make(RecordSchedulerRun::class);
        $recordSchedulerRun->handle(new CommandStarting(fake()->slug(), new ArrayInput([]), new NullOutput()));

        $schedulerLastRun = $this->app->make(SchedulerLastRun::class);
        $this->assertNull($schedulerLastRun->lastRanAt());
    }
}
