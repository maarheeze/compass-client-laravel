<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Maarheeze\CompassClient\CompassApiUrl;
use Maarheeze\CompassClient\Http\Requests\StatusRequest;
use Maarheeze\CompassClient\Status;

readonly class StatusController
{
    public function __construct(
        private CompassApiUrl $compassApiUrl,
        private Status $status,
    ) {
    }

    public function __invoke(StatusRequest $statusRequest): JsonResponse
    {
        if ($statusRequest->hasCompassApiUrl()) {
            $this->compassApiUrl->remember($statusRequest->compassApiUrl(), $statusRequest->compassApiUrlValidUntil());
        }

        return new JsonResponse($this->status->toArray());
    }
}
