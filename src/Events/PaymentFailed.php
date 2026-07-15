<?php

namespace RaftarPay\Events;

use RaftarPay\Support\CallbackResponse;

class PaymentFailed
{
    public function __construct(public CallbackResponse $response)
    {
    }
}
