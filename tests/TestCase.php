<?php

namespace RaftarPay\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use RaftarPay\RaftarPayServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [RaftarPayServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('raftarpay.environment', 'sandbox');
        $app['config']->set('raftarpay.gateways.jazzcash', [
            'merchant_id'    => 'MC10000',
            'password'       => 'test_password',
            'integrity_salt' => 'test_salt',
            'currency'       => 'PKR',
            'language'       => 'EN',
        ]);
        $app['config']->set('raftarpay.gateways.easypaisa', [
            'store_id' => '12345',
            'hash_key' => '1234567890123456',
            'currency' => 'PKR',
        ]);
    }
}
