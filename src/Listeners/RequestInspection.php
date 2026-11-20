<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient\Listeners;

use Illuminate\Config\Repository;
use Illuminate\Foundation\Events\MaintenanceModeDisabled;
use Illuminate\Foundation\Events\MaintenanceModeEnabled;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Maarheeze\CompassClient\CompassApiUrl;
use Maarheeze\CompassClient\SiteKey;

readonly class RequestInspection
{
    public function __construct(
        private CompassApiUrl $compassApiUrl,
        private Repository $config,
        private HttpFactory $http,
        private SiteKey $siteKey,
    ) {
    }

    public function handle(MaintenanceModeDisabled|MaintenanceModeEnabled $maintenanceModeChanged): void
    {
        $url = $this->compassApiUrl->url();

        if ($url === null) {
            return;
        }

        $siteKey = $this->siteKey->configured();

        if ($siteKey === null) {
            return;
        }

        try {
            $this->http
                ->withToken($siteKey)
                ->acceptJson()
                ->timeout($this->config->integer('compass-client.compass_api_timeout_seconds'))
                ->post($url);
        } catch (ConnectionException) {
            return;
        }
    }
}
