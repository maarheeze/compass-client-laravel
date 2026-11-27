<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient;

use BackedEnum;
use Illuminate\Config\Repository;
use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Foundation\MaintenanceMode;
use Illuminate\Routing\UrlGenerator;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

use function is_int;
use function is_string;
use function sprintf;

class Status
{
    private const int DEFAULT_FREQUENCY_MINUTES = 15;

    public function __construct(
        protected readonly Repository $config,
        protected readonly ConsoleKernel $consoleKernel,
        protected readonly MaintenanceMode $maintenanceMode,
        protected readonly Schedule $schedule,
        protected readonly SchedulerLastRun $schedulerLastRun,
        protected readonly UrlGenerator $urlGenerator,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'app_debug' => $this->config->boolean('app.debug'),
            'app_env' => $this->config->string('app.env'),
            'app_name' => $this->config->string('app.name'),
            'maintenance_mode' => $this->maintenanceMode->active(),
            'endpoints' => [
                ...$this->uptimeCheckPathEndpoints(),
                ...$this->uptimeCheckRouteEndpoints(),
            ],
            'scheduler' => [
                'last_ran_at' => $this->schedulerLastRun->lastRanAt()?->toIso8601String(),
                'tasks' => $this->scheduledTasks(),
            ],
            'queue_driver' => $this->queueDriver(),
        ];
    }

    /**
     * @return array{path: string, frequency_minutes: int}
     */
    private function endpoint(string $path, int $frequencyMinutes): array
    {
        return [
            'path' => $path,
            'frequency_minutes' => $frequencyMinutes,
        ];
    }

    private function loadConsoleSchedule(): void
    {
        $this->consoleKernel->all();
    }

    private function queueDriver(): string
    {
        $queueConnection = $this->config->string('queue.default');

        return $this->config->string(sprintf('queue.connections.%s.driver', $queueConnection));
    }

    private function routePath(mixed $routeName): string
    {
        if ($routeName instanceof BackedEnum) {
            return $this->urlGenerator->route($routeName, [], false);
        }

        if (is_string($routeName)) {
            return $this->urlGenerator->route($routeName, [], false);
        }

        throw new InvalidArgumentException('A route name must be a string or a backed enum.');
    }

    /**
     * @return array{command: string, expression: string}
     */
    private function scheduledTask(Event $event): array
    {
        return [
            'command' => $this->scheduledTaskCommand($event),
            'expression' => $event->expression,
        ];
    }

    private function scheduledTaskCommand(Event $event): string
    {
        if ($event instanceof CallbackEvent) {
            return $event->getSummaryForDisplay();
        }

        return Event::normalizeCommand(Assert::string($event->command));
    }

    /**
     * @return list<array{command: string, expression: string}>
     */
    private function scheduledTasks(): array
    {
        $this->loadConsoleSchedule();

        $scheduledTasks = [];

        foreach ($this->schedule->events() as $event) {
            $scheduledTasks[] = $this->scheduledTask($event);
        }

        return $scheduledTasks;
    }

    /**
     * @return list<array{path: string, frequency_minutes: int}>
     */
    private function uptimeCheckPathEndpoints(): array
    {
        $uptimeCheckPathEndpoints = [];

        foreach ($this->config->array('compass-client.uptime_checks.paths') as $key => $value) {
            $uptimeCheckPathEndpoints[] = is_int($key)
                ? $this->endpoint(Assert::string($value), self::DEFAULT_FREQUENCY_MINUTES)
                : $this->endpoint($key, Assert::positiveInteger($value));
        }

        return $uptimeCheckPathEndpoints;
    }

    /**
     * @return list<array{path: string, frequency_minutes: int}>
     */
    private function uptimeCheckRouteEndpoints(): array
    {
        $uptimeCheckRouteEndpoints = [];

        foreach ($this->config->array('compass-client.uptime_checks.routes') as $key => $value) {
            $uptimeCheckRouteEndpoints[] = is_int($key)
                ? $this->endpoint($this->routePath($value), self::DEFAULT_FREQUENCY_MINUTES)
                : $this->endpoint($this->routePath($key), Assert::positiveInteger($value));
        }

        return $uptimeCheckRouteEndpoints;
    }
}
