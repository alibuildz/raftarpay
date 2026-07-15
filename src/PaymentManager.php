<?php

namespace RaftarPay;

use Illuminate\Http\Request;
use RaftarPay\Contracts\Gateway;
use RaftarPay\Gateways\EasyPaisaGateway;
use RaftarPay\Gateways\JazzCashGateway;
use RaftarPay\Exceptions\RaftarPayException;
use RaftarPay\Support\ChargeRequest;
use RaftarPay\Support\ChargeResponse;
use RaftarPay\Support\CallbackResponse;

class PaymentManager
{
    /** @var array<string, class-string<Gateway>> */
    protected array $map = [
        'jazzcash'  => JazzCashGateway::class,
        'easypaisa' => EasyPaisaGateway::class,
    ];

    /** @var array<string, Gateway> */
    protected array $resolved = [];

    public function __construct(protected array $config)
    {
    }

    public function gateway(?string $name = null): Gateway
    {
        $name = $name ?: ($this->config['default'] ?? 'jazzcash');

        if (isset($this->resolved[$name])) {
            return $this->resolved[$name];
        }

        if (! isset($this->map[$name])) {
            throw RaftarPayException::unknownGateway($name);
        }

        $class = $this->map[$name];
        $gatewayConfig = $this->config['gateways'][$name] ?? [];
        $environment = $this->config['environment'] ?? 'sandbox';

        return $this->resolved[$name] = new $class($gatewayConfig, $environment);
    }

    /**
     * Register or override a gateway driver at runtime.
     *
     * @param  class-string<Gateway>  $class
     */
    public function extend(string $name, string $class): static
    {
        $this->map[$name] = $class;
        unset($this->resolved[$name]);

        return $this;
    }

    public function available(): array
    {
        return array_keys($this->map);
    }

    // --- Convenience passthroughs to the default gateway -------------------

    public function charge(array|ChargeRequest $request, ?string $gateway = null): ChargeResponse
    {
        $request = $request instanceof ChargeRequest ? $request : ChargeRequest::make($request);

        return $this->gateway($gateway)->charge($request);
    }

    public function verify(string $reference, ?string $gateway = null): CallbackResponse
    {
        return $this->gateway($gateway)->verify($reference);
    }

    public function handleCallback(Request $request, ?string $gateway = null): CallbackResponse
    {
        return $this->gateway($gateway)->handleCallback($request);
    }
}
