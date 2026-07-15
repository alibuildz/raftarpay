<?php

namespace RaftarPay\Facades;

use Illuminate\Support\Facades\Facade;
use RaftarPay\Contracts\Gateway;
use RaftarPay\Support\ChargeResponse;
use RaftarPay\Support\CallbackResponse;

/**
 * Convenience alias for the RaftarPay facade.
 *
 * @method static Gateway gateway(?string $name = null)
 * @method static ChargeResponse charge(array|\RaftarPay\Support\ChargeRequest $request, ?string $gateway = null)
 * @method static CallbackResponse verify(string $reference, ?string $gateway = null)
 * @method static CallbackResponse handleCallback(\Illuminate\Http\Request $request, ?string $gateway = null)
 * @method static \RaftarPay\PaymentManager extend(string $name, string $class)
 * @method static array available()
 *
 * @see \RaftarPay\PaymentManager
 */
class Payment extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'raftarpay';
    }
}
