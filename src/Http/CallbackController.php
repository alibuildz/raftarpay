<?php

namespace RaftarPay\Http;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use RaftarPay\Events\PaymentCompleted;
use RaftarPay\Events\PaymentFailed;
use RaftarPay\Facades\RaftarPay;
use RaftarPay\Models\RaftarPayTransaction;

class CallbackController extends Controller
{
    public function handle(Request $request, string $gateway)
    {
        $result = RaftarPay::handleCallback($request, $gateway);

        if (config('raftarpay.logging.enabled', true) && $result->reference !== '') {
            $transaction = RaftarPayTransaction::query()
                ->where('reference', $result->reference)
                ->first();

            if ($transaction) {
                $result->isPaid()
                    ? $transaction->markPaid($result->raw)
                    : $transaction->markFailed($result->raw);
            }
        }

        event($result->isPaid() ? new PaymentCompleted($result) : new PaymentFailed($result));

        return response()->json($result->toArray());
    }
}
