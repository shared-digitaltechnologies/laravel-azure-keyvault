<?php

namespace Shrd\Laravel\Azure\KeyVault\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            'Shrd\Laravel\Azure\KeyVault\ServiceProvider'
        ];
    }
}
