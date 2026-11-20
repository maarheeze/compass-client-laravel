<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient;

use Illuminate\Config\Repository;

use function is_string;

readonly class SiteKey
{
    public function __construct(
        private Repository $config,
    ) {
    }

    public function configured(): ?string
    {
        $configuredSiteKey = $this->config->get('compass-client.key');

        if (!is_string($configuredSiteKey)) {
            return null;
        }

        if ($configuredSiteKey === '') {
            return null;
        }

        return $configuredSiteKey;
    }
}
