<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient;

use Illuminate\Config\Repository;

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
            'endpoints' => $this->config->array('compass-client.endpoints'),
        ];
    }
}
