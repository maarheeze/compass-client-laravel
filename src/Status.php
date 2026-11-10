<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient;

use BackedEnum;
use Illuminate\Config\Repository;
use Illuminate\Routing\UrlGenerator;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

use function array_map;
use function is_string;

class Status
{
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
                ...$this->config->array('compass-client.uptime_checks.paths'),
                ...$this->uptimeCheckRoutePaths(),
            ],
        ];
    }

    /**
     * @return list<string>
     */
    private function uptimeCheckRoutePaths(): array
    {
        $uptimeCheckRouteNames = $this->config->array('compass-client.uptime_checks.routes');
        Assert::isList($uptimeCheckRouteNames);

        return array_map(function (mixed $routeName): string {
            if ($routeName instanceof BackedEnum) {
                return $this->urlGenerator->route($routeName, [], false);
            }

            if (is_string($routeName)) {
                return $this->urlGenerator->route($routeName, [], false);
            }

            throw new InvalidArgumentException('A route name must be a string or a backed enum.');
        },
        $uptimeCheckRouteNames);
    }
}
