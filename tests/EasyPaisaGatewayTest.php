<?php

namespace RaftarPay\Tests;

use RaftarPay\Facades\RaftarPay;
use RaftarPay\Gateways\EasyPaisaGateway;
use RaftarPay\Support\ChargeResponse;

class EasyPaisaGatewayTest extends TestCase
{
    protected function gateway(): EasyPaisaGateway
    {
        return new EasyPaisaGateway([
            'store_id' => '12345',
            'hash_key' => '1234567890123456',
            'currency' => 'PKR',
        ], 'sandbox');
    }

    public function test_charge_returns_form_post_with_hashed_request(): void
    {
        $response = RaftarPay::charge([
            'amount'     => 1000,
            'reference'  => 'RP-EP-1',
            'return_url' => 'https://example.com/return',
        ], 'easypaisa');

        $this->assertInstanceOf(ChargeResponse::class, $response);
        $this->assertTrue($response->isFormPost());
        $this->assertSame('12345', $response->formFields['storeId']);
        $this->assertSame('1000.0', $response->formFields['amount']);
        $this->assertArrayHasKey('merchantHashedReq', $response->formFields);
        $this->assertNotEmpty($response->formFields['merchantHashedReq']);
    }

    public function test_hashed_request_is_valid_base64_and_decrypts(): void
    {
        $gateway = $this->gateway();

        $params = [
            'amount'      => '1000.0',
            'orderRefNum' => 'RP-EP-1',
            'storeId'     => '12345',
            'postBackURL' => 'https://example.com/return',
        ];

        $hashed = $gateway->encryptRequest($params);
        $decoded = base64_decode($hashed, true);

        $this->assertNotFalse($decoded);

        $plain = openssl_decrypt($decoded, 'AES-128-ECB', '1234567890123456', OPENSSL_RAW_DATA);

        $this->assertStringContainsString('amount=1000.0', $plain);
        $this->assertStringContainsString('orderRefNum=RP-EP-1', $plain);
        // fields must be sorted alphabetically
        $this->assertStringStartsWith('amount=', $plain);
    }
}
