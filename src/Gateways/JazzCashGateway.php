<?php

namespace RaftarPay\Gateways;

use Illuminate\Http\Request;
use RaftarPay\Support\ChargeRequest;
use RaftarPay\Support\ChargeResponse;
use RaftarPay\Support\CallbackResponse;

class JazzCashGateway extends AbstractGateway
{
    public const GATEWAY_NAME = 'jazzcash';

    protected const SANDBOX_URL = 'https://sandbox.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform/';
    protected const PRODUCTION_URL = 'https://payments.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform/';

    protected const VERSION = '1.1';

    public function charge(ChargeRequest $request): ChargeResponse
    {
        $now = now();

        $fields = [
            'pp_Version'            => self::VERSION,
            'pp_TxnType'            => '',
            'pp_Language'           => $this->config('language', false) ?: 'EN',
            'pp_MerchantID'         => $this->config('merchant_id'),
            'pp_SubMerchantID'      => '',
            'pp_Password'           => $this->config('password'),
            'pp_BankID'             => '',
            'pp_ProductID'          => '',
            'pp_TxnRefNo'           => $request->reference,
            'pp_Amount'             => (string) $request->amountInPaisa(),
            'pp_TxnCurrency'        => $this->config('currency', false) ?: 'PKR',
            'pp_TxnDateTime'        => $now->format('YmdHis'),
            'pp_BillReference'      => $request->reference,
            'pp_Description'        => $request->description ?: 'Payment',
            'pp_TxnExpiryDateTime'  => $now->copy()->addHour()->format('YmdHis'),
            'pp_ReturnURL'          => $request->returnUrl ?: $this->defaultReturnUrl(),
            'ppmpf_1'               => $request->customerName ?? '',
            'ppmpf_2'               => $request->customerEmail ?? '',
            'ppmpf_3'               => $request->customerPhone ?? '',
        ];

        $fields['pp_SecureHash'] = $this->secureHash($fields);

        return new ChargeResponse(
            gateway: $this->name(),
            reference: $request->reference,
            action: ChargeResponse::ACTION_FORM_POST,
            formFields: $fields,
            formAction: $this->endpoint(),
            successful: true,
            message: 'Redirecting to JazzCash checkout.',
            raw: $fields,
        );
    }

    public function verify(string $reference): CallbackResponse
    {
        // JazzCash confirms the result via the return post; a dedicated status
        // inquiry API also exists. We return pending until the callback lands.
        return new CallbackResponse(
            gateway: $this->name(),
            reference: $reference,
            status: CallbackResponse::STATUS_PENDING,
            message: 'Awaiting JazzCash callback.',
        );
    }

    public function handleCallback(Request $request): CallbackResponse
    {
        $data = $request->all();

        $received = (string) ($data['pp_SecureHash'] ?? '');
        $expected = $this->secureHash($data);
        $signatureValid = $received !== '' && $this->hashEquals($expected, $received);

        $responseCode = (string) ($data['pp_ResponseCode'] ?? '');
        $paid = $signatureValid && $responseCode === '000';

        return new CallbackResponse(
            gateway: $this->name(),
            reference: (string) ($data['pp_TxnRefNo'] ?? $data['pp_BillReference'] ?? ''),
            status: $paid ? CallbackResponse::STATUS_PAID : CallbackResponse::STATUS_FAILED,
            amount: isset($data['pp_Amount']) ? ((int) $data['pp_Amount']) / 100 : 0,
            gatewayReference: $data['pp_RetreivalReferenceNo'] ?? $data['pp_AuthCode'] ?? null,
            message: $data['pp_ResponseMessage'] ?? null,
            signatureValid: $signatureValid,
            raw: $data,
        );
    }

    /**
     * Official JazzCash HMAC-SHA256 secure hash:
     *   1. Take every pp_* / ppmpf_* field that is non-empty (excluding the hash itself).
     *   2. Sort them by key (case-insensitive).
     *   3. Join their values with "&", prefixed by the Integrity Salt.
     *   4. HMAC-SHA256 with the Integrity Salt as key, uppercase hex.
     */
    public function secureHash(array $fields): string
    {
        $salt = (string) $this->config('integrity_salt');

        $filtered = [];
        foreach ($fields as $key => $value) {
            if ($key === 'pp_SecureHash') {
                continue;
            }
            if (! (str_starts_with($key, 'pp_') || str_starts_with($key, 'ppmpf_'))) {
                continue;
            }
            if ($value === null || $value === '') {
                continue;
            }
            $filtered[$key] = $value;
        }

        uksort($filtered, 'strcasecmp');

        $message = $salt . '&' . implode('&', array_values($filtered));

        return strtoupper(hash_hmac('sha256', $message, $salt));
    }

    protected function endpoint(): string
    {
        return $this->isProduction() ? self::PRODUCTION_URL : self::SANDBOX_URL;
    }

    protected function defaultReturnUrl(): string
    {
        return url(config('raftarpay.routes.prefix', 'raftarpay') . '/jazzcash/callback');
    }
}
