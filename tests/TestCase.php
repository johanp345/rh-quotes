<?php

namespace RH\Quotes\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return ['RH\Quotes\Providers\QuotesServiceProvider'];
    }
}