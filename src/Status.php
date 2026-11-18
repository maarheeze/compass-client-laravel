<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient;

use BackedEnum;
use Illuminate\Config\Repository;
use Illuminate\Routing\UrlGenerator;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

use function is_int;
use function is_string;

class Status
{
    private const int DEFAULT_FREQUENCY_MINUTES = 15;

    public function __construct(
        protected readonly Repository $config,
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
            'endpoints' => [
                ...$this->uptimeCheckPathEndpoints(),
                ...$this->uptimeCheckRouteEndpoints(),
            ],
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
