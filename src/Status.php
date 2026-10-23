<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient;

use Illuminate\Config\Repository;

use function array_map;
use function explode;
use function trim;

class Status
{
    public function __construct(
        protected readonly Repository $config,
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
            'endpoints' => array_map(trim(...), explode(',', $this->config->string('compass-client.endpoints'))),
        ];
    }
}
