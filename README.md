# RaftarPay 🇵🇰⚡

**A clean, Laravel-native unified API for Pakistani payment gateways.**

Integrating payments in Pakistan usually means learning a different SDK, hashing scheme and callback format for every provider. RaftarPay gives you **one consistent API** for **JazzCash** and **EasyPaisa** — with the banks' official security (HMAC-SHA256 / AES-128 request signing and signature-verified callbacks) baked in.

```php
use RaftarPay\Facades\Payment;

return Payment::gateway('jazzcash')->charge([
    'amount'      => 1500,              // PKR
    'reference'   => 'ORDER-1001',
    'description' => 'Premium plan',
    'return_url'  => route('checkout.callback'),
])->send();                            // redirects the customer to the bank
```

---

## ✨ Features

- ✅ **Unified API** across gateways — swap `jazzcash` ↔ `easypaisa` with one string
- ✅ **JazzCash** support (official `pp_SecureHash` HMAC-SHA256 page-redirect flow)
- ✅ **EasyPaisa** support (Easypay `merchantHashedReq` AES-128 hosted checkout)
- ✅ **Signature-verified callbacks** — tampered payloads are rejected automatically
- ✅ **Automatic transaction logging** (`raftarpay_transactions` table)
- ✅ **Events** — `PaymentCompleted` / `PaymentFailed`
- ✅ **Sandbox & production** environments via one config flag
- ✅ **Extensible** — register your own gateway driver with `Payment::extend()`
- ✅ **Fully tested** (PHPUnit + Orchestra Testbench)

---

## 📦 Installation

```bash
composer require raftarpay/raftarpay
php artisan raftarpay:install
```

`raftarpay:install` publishes the config (`config/raftarpay.php`), publishes and runs the migration.

Then add your merchant credentials to `.env`:

```env
RAFTARPAY_GATEWAY=jazzcash
RAFTARPAY_ENV=sandbox

# JazzCash (JazzCash Business account)
JAZZCASH_MERCHANT_ID=MC00000
JAZZCASH_PASSWORD=xxxxxxxx
JAZZCASH_INTEGRITY_SALT=xxxxxxxx

# EasyPaisa (Telenor Microfinance Bank merchant)
EASYPAISA_STORE_ID=00000
EASYPAISA_HASH_KEY=xxxxxxxxxxxxxxxx
```

> You need your **own merchant account** with each provider to accept real money.
> JazzCash & EasyPaisa both offer **public sandboxes**, so you can test RaftarPay end-to-end before going live.

---

## 🚀 Usage

### 1. Start a payment

```php
use RaftarPay\Facades\Payment;

$response = Payment::charge([
    'amount'         => 2500,
    'reference'      => 'ORDER-'.$order->id,
    'description'    => 'Order #'.$order->id,
    'customer_email' => $order->email,
    'customer_phone' => $order->phone,
    'return_url'     => route('payments.callback'),
], 'jazzcash');

return $response->send();   // redirect / auto-submit form to the gateway
```

### 2. Handle the callback

RaftarPay auto-registers callback routes at:

```
POST /raftarpay/{gateway}/callback
```

It verifies the signature, updates the transaction, and fires an event. Listen for it:

```php
use RaftarPay\Events\PaymentCompleted;

Event::listen(function (PaymentCompleted $e) {
    $order = Order::where('reference', $e->response->reference)->first();
    $order?->markPaid();
});
```

Prefer to handle it yourself? Verify any request manually:

```php
$result = Payment::handleCallback($request, 'jazzcash');

if ($result->isPaid()) {
    // ✅ signature valid + bank approved
}
```

### 3. `mode` — 2D vs 3D

Set `mode` per gateway in the config:

- `3d` *(default, recommended)* — 3D-Secure / OTP verification by the customer's bank.
- `2d` — direct charge without OTP (only if your merchant profile is approved for it).

---

## 🧩 Adding your own gateway

```php
Payment::extend('mygateway', \App\Payments\MyGateway::class);
```

Any class implementing `RaftarPay\Contracts\Gateway` works.

---

## 🔒 Security

- JazzCash requests/responses are signed and verified with **HMAC-SHA256** over the integrity salt + sorted fields.
- EasyPaisa requests are **AES-128-ECB** encrypted with your Hash Key.
- Callback signatures are compared in **constant time** (`hash_equals`).
- Never commit your merchant credentials — keep them in `.env`.

---

## 🧪 Testing

```bash
composer install
vendor/bin/phpunit
```

---

## 🤝 Contributing

RaftarPay is open source and community-driven. Issues, new gateway drivers, docs and fixes are all welcome — open a PR.

## 📄 License

MIT © Muhammad Ali ([@alibuildz](https://github.com/alibuildz) · [LinkedIn](https://www.linkedin.com/in/alibuilds/))
