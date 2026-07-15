<?php

namespace RaftarPay\Exceptions;

use Exception;

class RaftarPayException extends Exception
{
    public static function missingConfig(string $gateway, string $key): self
    {
        return new self("RaftarPay: missing config '{$key}' for gateway '{$gateway}'. Add your merchant credentials to config/raftarpay.php or your .env file.");
    }

    public static function unknownGateway(string $gateway): self
    {
        return new self("RaftarPay: unknown gateway '{$gateway}'. Supported: jazzcash, easypaisa, kuickpay, faysal, meezan.");
    }

    public static function invalidSignature(string $gateway): self
    {
        return new self("RaftarPay: callback signature verification failed for gateway '{$gateway}'. The payload may have been tampered with.");
    }
}
