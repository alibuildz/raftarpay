<?php

namespace RaftarPay\Support;

class ChargeRequest
{
    public string $reference;
    public int|float $amount;
    public string $currency;
    public ?string $description;
    public ?string $customerName;
    public ?string $customerEmail;
    public ?string $customerPhone;
    public ?string $returnUrl;
    public array $meta;

    public function __construct(array $attributes = [])
    {
        $this->reference     = $attributes['reference'] ?? self::generateReference();
        $this->amount        = $attributes['amount'] ?? 0;
        $this->currency      = $attributes['currency'] ?? 'PKR';
        $this->description   = $attributes['description'] ?? null;
        $this->customerName  = $attributes['customer_name'] ?? null;
        $this->customerEmail = $attributes['customer_email'] ?? null;
        $this->customerPhone = $attributes['customer_phone'] ?? null;
        $this->returnUrl     = $attributes['return_url'] ?? null;
        $this->meta          = $attributes['meta'] ?? [];
    }

    public static function make(array $attributes = []): self
    {
        return new self($attributes);
    }

    /**
     * Amount in the smallest unit the gateways expect (PKR has no minor unit
     * for these APIs, but JazzCash wants amount * 100). Helpers below make the
     * intent explicit at the call site.
     */
    public function amountInPaisa(): int
    {
        return (int) round($this->amount * 100);
    }

    public function amountAsInteger(): int
    {
        return (int) round($this->amount);
    }

    public static function generateReference(): string
    {
        return 'RP' . date('YmdHis') . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }
}
