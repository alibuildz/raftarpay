<?php

namespace RaftarPay\Tests;

use Illuminate\Http\Request;
use RaftarPay\Facades\RaftarPay;
use RaftarPay\Gateways\JazzCashGateway;
use RaftarPay\Support\CallbackResponse;
use RaftarPay\Support\ChargeResponse;

class JazzCashGatewayTest extends TestCase
{
    protected function gateway(): JazzCashGateway
    {
        return new JazzCashGateway([
            'merchant_id'    => 'MC10000',
            'password'       => 'test_password',
            'integrity_salt' => 'test_salt',
            'currency'       => 'PKR',
            'language'       => 'EN',
        ], 'sandbox');
    }

    public function test_charge_returns_form_post_with_secure_hash(): void
    {
        $response = RaftarPay::charge([
            'amount'      => 500,
            'reference'   => 'RP-TEST-1',
            'description' => 'Test order',
            'return_url'  => 'https://example.com/return',
        ], 'jazzcash');

        $this->assertInstanceOf(ChargeResponse::class, $response);
        $this->assertTrue($response->isFormPost());
        $this->assertSame('50000', $response->formFields['pp_Amount']); // paisa
        $this->assertArrayHasKey('pp_SecureHash', $response->formFields);
        $this->assertSame(64, strlen($response->formFields['pp_SecureHash']));
    }

    public function test_secure_hash_is_deterministic_and_uppercase(): void
    {
        $gateway = $this->gateway();

        $fields = [
            'pp_MerchantID' => 'MC10000',
            'pp_Amount'     => '50000',
            'pp_TxnRefNo'   => 'RP-1',
        ];

        $hash = $gateway->secureHash($fields);

        $this->assertSame($hash, $gateway->secureHash($fields));
        $this->assertSame(strtoupper($hash), $hash);
    }

    public function test_callback_with_valid_hash_and_000_is_paid(): void
    {
        $gateway = $this->gateway();

        $data = [
            'pp_TxnRefNo'    => 'RP-1',
            'pp_Amount'      => '50000',
            'pp_ResponseCode' => '000',
            'pp_ResponseMessage' => 'Success',
        ];
        $data['pp_SecureHash'] = $gateway->secureHash($data);

        $result = $gateway->handleCallback(Request::create('/cb', 'POST', $data));

        $this->assertInstanceOf(CallbackResponse::class, $result);
        $this->assertTrue($result->signatureValid);
        $this->assertTrue($result->isPaid());
        $this->assertSame(500.0, (float) $result->amount);
    }

    public function test_callback_with_tampered_hash_is_rejected(): void
    {
        $gateway = $this->gateway();

        $data = [
            'pp_TxnRefNo'     => 'RP-1',
            'pp_Amount'       => '50000',
            'pp_ResponseCode' => '000',
            'pp_SecureHash'   => 'DEADBEEF',
        ];

        $result = $gateway->handleCallback(Request::create('/cb', 'POST', $data));

        $this->assertFalse($result->signatureValid);
        $this->assertFalse($result->isPaid());
    }
}
