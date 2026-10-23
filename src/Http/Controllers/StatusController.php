<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Maarheeze\CompassClient\Status;

readonly class StatusController
{
    public function __construct(
        private Status $status,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        return new JsonResponse($this->status->toArray());
    }
}
