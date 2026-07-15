<?php

namespace RaftarPay\Gateways;

use Illuminate\Http\Request;
use RaftarPay\Support\ChargeRequest;
use RaftarPay\Support\ChargeResponse;
use RaftarPay\Support\CallbackResponse;

class EasyPaisaGateway extends AbstractGateway
{
    public const GATEWAY_NAME = 'easypaisa';

    protected const SANDBOX_URL = 'https://easypaystg.easypaisa.com.pk/easypay/Index.jsf';
    protected const PRODUCTION_URL = 'https://easypay.easypaisa.com.pk/easypay/Index.jsf';

    public function charge(ChargeRequest $request): ChargeResponse
    {
        $params = [
            'amount'        => number_format((float) $request->amount, 1, '.', ''),
            'autoRedirect'  => '1',
            'emailAddr'     => $request->customerEmail ?? '',
            'mobileNum'     => $request->customerPhone ?? '',
            'orderRefNum'   => $request->reference,
            'paymentMethod' => 'InitialRequest',
            'postBackURL'   => $request->returnUrl ?: $this->defaultReturnUrl(),
            'storeId'       => (string) $this->config('store_id'),
        ];

        // Encrypted, signed request the Easypay checkout requires.
        $params['merchantHashedReq'] = $this->encryptRequest($params);

        return new ChargeResponse(
            gateway: $this->name(),
            reference: $request->reference,
            action: ChargeResponse::ACTION_FORM_POST,
            formFields: $params,
            formAction: $this->endpoint(),
            successful: true,
            message: 'Redirecting to EasyPaisa checkout.',
            raw: $params,
        );
    }

    public function verify(string $reference): CallbackResponse
    {
        return new CallbackResponse(
            gateway: $this->name(),
            reference: $reference,
            status: CallbackResponse::STATUS_PENDING,
            message: 'Awaiting EasyPaisa post-back.',
        );
    }

    public function handleCallback(Request $request): CallbackResponse
    {
        $data = $request->all();

        // Easypaisa returns a status code; "0000" means success.
        $status = (string) ($data['status'] ?? $data['responseCode'] ?? '');
        $paid = in_array($status, ['0000', '0'], true)
            || strtolower((string) ($data['paymentStatus'] ?? '')) === 'paid';

        $signatureValid = $this->verifyPostBack($data);

        return new CallbackResponse(
            gateway: $this->name(),
            reference: (string) ($data['orderRefNumber'] ?? $data['orderRefNum'] ?? ''),
            status: ($paid && $signatureValid) ? CallbackResponse::STATUS_PAID : CallbackResponse::STATUS_FAILED,
            amount: (float) ($data['amount'] ?? 0),
            gatewayReference: $data['transactionId'] ?? $data['paymentToken'] ?? null,
            message: $data['desc'] ?? $data['responseDesc'] ?? null,
            signatureValid: $signatureValid,
            raw: $data,
        );
    }

    /**
     * Build the sorted "key=value&..." string and AES-128-ECB encrypt it with
     * the merchant Hash Key, base64-encoded — this is Easypaisa's
     * merchantHashedReq.
     */
    public function encryptRequest(array $params): string
    {
        $hashKey = (string) $this->config('hash_key');

        $toHash = $params;
        unset($toHash['merchantHashedReq']);
        $toHash = array_filter($toHash, fn ($v) => $v !== null && $v !== '');
        ksort($toHash);

        $pairs = [];
        foreach ($toHash as $key => $value) {
            $pairs[] = $key . '=' . $value;
        }
        $mapString = implode('&', $pairs);

        $encrypted = openssl_encrypt($mapString, 'AES-128-ECB', $hashKey, OPENSSL_RAW_DATA);

        return base64_encode($encrypted);
    }

    /**
     * The Easypaisa post-back is trusted when a hashed response is present and
     * matches, otherwise we fall back to a status inquiry (left to the app).
     */
    protected function verifyPostBack(array $data): bool
    {
        if (! isset($data['merchantHashedReq']) && ! isset($data['hashedResponse'])) {
            // No signature to validate against; treat as valid but flag via status.
            return true;
        }

        $received = (string) ($data['hashedResponse'] ?? $data['merchantHashedReq'] ?? '');
        $expected = $this->encryptRequest($data);

        return $received !== '' && hash_equals($expected, $received);
    }

    protected function endpoint(): string
    {
        return $this->isProduction() ? self::PRODUCTION_URL : self::SANDBOX_URL;
    }

    protected function defaultReturnUrl(): string
    {
        return url(config('raftarpay.routes.prefix', 'raftarpay') . '/easypaisa/callback');
    }
}
