<?php

namespace RaftarPay\Events;

use RaftarPay\Support\CallbackResponse;

class PaymentCompleted
{
    public function __construct(public CallbackResponse $response)
    {
    }
}
