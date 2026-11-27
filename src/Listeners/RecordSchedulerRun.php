<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient\Listeners;

use Carbon\CarbonImmutable;
use Illuminate\Console\Events\CommandStarting;
use Maarheeze\CompassClient\SchedulerLastRun;
use Psr\Clock\ClockInterface;

readonly class RecordSchedulerRun
{
    private const string SCHEDULE_RUN_COMMAND = 'schedule:run';

    public function __construct(
        private ClockInterface $clock,
        private SchedulerLastRun $schedulerLastRun,
    ) {
    }

    public function handle(CommandStarting $commandStarting): void
    {
        if ($commandStarting->command !== self::SCHEDULE_RUN_COMMAND) {
            return;
        }

        $this->schedulerLastRun->remember(CarbonImmutable::instance($this->clock->now()));
    }
}
