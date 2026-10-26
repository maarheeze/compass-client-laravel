<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient\Tests;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Maarheeze\CompassClient\CompassClientServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * @var bool
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $enablesPackageDiscoveries = true;

    public function setConfiguration(string $key, mixed $value): void
    {
        $configuration = $this->app->make(Repository::class);
        $this->assertTrue($configuration->has($key));

        $configuration->set($key, $value);
    }

    /**
     * @param Application $app
     *
     * @return list<class-string>
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    protected function getPackageProviders($app): array
    {
        return [
            CompassClientServiceProvider::class,
        ];
    }
}
