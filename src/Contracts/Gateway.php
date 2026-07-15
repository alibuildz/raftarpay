<?php

namespace RaftarPay\Contracts;

use Illuminate\Http\Request;
use RaftarPay\Support\ChargeRequest;
use RaftarPay\Support\ChargeResponse;
use RaftarPay\Support\CallbackResponse;

interface Gateway
{
    /**
     * Start a payment. Depending on the gateway/mode this either returns a
     * redirect to the bank's hosted checkout or the result of a direct charge.
     */
    public function charge(ChargeRequest $request): ChargeResponse;

    /**
     * Verify the outcome of a transaction with the gateway (server side),
     * typically using the reference returned by charge().
     */
    public function verify(string $reference): CallbackResponse;

    /**
     * Handle the server-to-server callback / return redirect from the gateway,
     * validating its signature before trusting the payload.
     */
    public function handleCallback(Request $request): CallbackResponse;

    /**
     * The gateway's short name, e.g. "jazzcash".
     */
    public function name(): string;
}
