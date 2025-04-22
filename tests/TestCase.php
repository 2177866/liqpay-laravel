<?php

namespace Tests;

use Alyakin\LiqPayLaravel\LiqPayServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            LiqPayServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('liqpay.public_key', 'test_public');
        $app['config']->set('liqpay.private_key', 'test_private');
    }
}
