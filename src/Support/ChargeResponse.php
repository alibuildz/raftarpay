<?php

namespace RaftarPay\Support;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class ChargeResponse
{
    public const ACTION_REDIRECT = 'redirect';
    public const ACTION_FORM_POST = 'form_post';
    public const ACTION_COMPLETED = 'completed';

    public function __construct(
        public string $gateway,
        public string $reference,
        public string $action,
        public ?string $redirectUrl = null,
        public array $formFields = [],
        public ?string $formAction = null,
        public bool $successful = false,
        public ?string $message = null,
        public array $raw = [],
    ) {
    }

    public function isRedirect(): bool
    {
        return $this->action === self::ACTION_REDIRECT;
    }

    public function isFormPost(): bool
    {
        return $this->action === self::ACTION_FORM_POST;
    }

    public function isCompleted(): bool
    {
        return $this->action === self::ACTION_COMPLETED;
    }

    /**
     * Send the customer to the gateway. For a redirect this returns a Laravel
     * redirect; for a form-post gateway it renders a self-submitting form.
     */
    public function send(): RedirectResponse|string
    {
        if ($this->isRedirect() && $this->redirectUrl) {
            return Redirect::away($this->redirectUrl);
        }

        if ($this->isFormPost() && $this->formAction) {
            return $this->autoSubmitForm();
        }

        return Redirect::back();
    }

    public function autoSubmitForm(): string
    {
        $inputs = '';
        foreach ($this->formFields as $key => $value) {
            $inputs .= sprintf(
                '<input type="hidden" name="%s" value="%s">',
                htmlspecialchars((string) $key, ENT_QUOTES),
                htmlspecialchars((string) $value, ENT_QUOTES)
            );
        }

        return '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Redirecting…</title></head>'
            . '<body onload="document.forms[0].submit()">'
            . '<form method="POST" action="' . htmlspecialchars((string) $this->formAction, ENT_QUOTES) . '">'
            . $inputs
            . '<noscript><button type="submit">Continue to payment</button></noscript>'
            . '</form></body></html>';
    }

    public function toArray(): array
    {
        return [
            'gateway'      => $this->gateway,
            'reference'    => $this->reference,
            'action'       => $this->action,
            'redirect_url' => $this->redirectUrl,
            'form_action'  => $this->formAction,
            'successful'   => $this->successful,
            'message'      => $this->message,
        ];
    }
}
