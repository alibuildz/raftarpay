<?php

namespace RaftarPay\Tests;

use RaftarPay\Exceptions\RaftarPayException;
use RaftarPay\Facades\Payment;
use RaftarPay\Gateways\EasyPaisaGateway;
use RaftarPay\Gateways\JazzCashGateway;

class PaymentManagerTest extends TestCase
{
    public function test_it_resolves_known_gateways(): void
    {
        $this->assertInstanceOf(JazzCashGateway::class, Payment::gateway('jazzcash'));
        $this->assertInstanceOf(EasyPaisaGateway::class, Payment::gateway('easypaisa'));
    }

    public function test_it_lists_available_gateways(): void
    {
        $this->assertEqualsCanonicalizing(['jazzcash', 'easypaisa'], Payment::available());
    }

    public function test_unknown_gateway_throws(): void
    {
        $this->expectException(RaftarPayException::class);
        Payment::gateway('paypal');
    }

    public function test_missing_config_throws_helpful_error(): void
    {
        config()->set('raftarpay.gateways.jazzcash.integrity_salt', null);

        $this->expectException(RaftarPayException::class);
        $this->expectExceptionMessageMatches('/integrity_salt/');

        Payment::charge(['amount' => 100, 'reference' => 'x'], 'jazzcash');
    }
}
