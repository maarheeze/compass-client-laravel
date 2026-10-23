<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient\Http\Controllers;

use Illuminate\Config\Repository;
use Illuminate\Http\JsonResponse;

use function array_map;
use function explode;
use function trim;

readonly class StatusController
{
    public function __construct(
        private Repository $config,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        return new JsonResponse([
            'app_debug' => $this->config->boolean('app.debug'),
            'app_env' => $this->config->string('app.env'),
            'app_name' => $this->config->string('app.name'),
            'pages' => array_map(trim(...), explode(',', $this->config->string('compass-client.pages'))),
        ]);
    }
}
