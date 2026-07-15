<?php

namespace RaftarPay\Models;

use Illuminate\Database\Eloquent\Model;

class RaftarPayTransaction extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount'          => 'decimal:2',
        'request_payload' => 'array',
        'response_payload' => 'array',
    ];

    public function getTable(): string
    {
        return config('raftarpay.logging.table', 'raftarpay_transactions');
    }

    public function markPaid(array $response = []): void
    {
        $this->update([
            'status'           => 'paid',
            'response_payload' => $response ?: $this->response_payload,
            'paid_at'          => now(),
        ]);
    }

    public function markFailed(array $response = []): void
    {
        $this->update([
            'status'           => 'failed',
            'response_payload' => $response ?: $this->response_payload,
        ]);
    }
}
