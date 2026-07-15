<?php

namespace RaftarPay\Support;

class CallbackResponse
{
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';
    public const STATUS_PENDING = 'pending';

    public function __construct(
        public string $gateway,
        public string $reference,
        public string $status,
        public int|float $amount = 0,
        public ?string $gatewayReference = null,
        public ?string $message = null,
        public bool $signatureValid = true,
        public array $raw = [],
    ) {
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID && $this->signatureValid;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function toArray(): array
    {
        return [
            'gateway'           => $this->gateway,
            'reference'         => $this->reference,
            'status'            => $this->status,
            'amount'            => $this->amount,
            'gateway_reference' => $this->gatewayReference,
            'message'           => $this->message,
            'signature_valid'   => $this->signatureValid,
        ];
    }
}
