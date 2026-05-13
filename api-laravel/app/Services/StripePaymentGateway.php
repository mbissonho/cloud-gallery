<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use Stripe\Checkout\Session;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;

class StripePaymentGateway implements PaymentGatewayInterface
{
    /**
     * Map Laravel locale codes to Stripe Checkout locale codes.
     * Stripe rejects 'pt_BR' but accepts 'pt-BR'.
     * Unknown locales fall back to 'auto' (Stripe detects from the browser).
     */
    private const LOCALE_MAP = [
        'en'    => 'en',
        'pt_BR' => 'pt-BR',
    ];

    public function __construct()
    {
        Stripe::setApiKey(config('checkout.stripe.secret_key'));
    }

    public function createCheckoutSession(array $params): array
    {
        $sessionParams = [
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => $params['currency'],
                    'product_data' => [
                        'name' => $params['image_title'],
                    ],
                    'unit_amount' => $params['amount_cents'],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $params['success_url'],
            'cancel_url' => $params['cancel_url'],
            'metadata' => $params['metadata'],
            'locale' => self::LOCALE_MAP[$params['locale'] ?? ''] ?? 'auto',
        ];

        if (!empty($params['customer_email'])) {
            $sessionParams['customer_email'] = $params['customer_email'];
        }

        $session = Session::create($sessionParams);

        return [
            'session_id' => $session->id,
            'checkout_url' => $session->url,
        ];
    }

    public function parseWebhookPayload(string $payload, string $signature): ?array
    {
        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                config('checkout.stripe.webhook_secret')
            );
        } catch (SignatureVerificationException) {
            return null;
        }

        if ($event->type !== 'checkout.session.completed') {
            return null;
        }

        $session = $event->data->object;

        return [
            'event_type' => 'checkout.completed',
            'session_id' => $session->id,
            'payment_id' => $session->payment_intent,
        ];
    }

    public function verifySessionPayment(string $sessionId): ?array
    {
        try {
            $session = Session::retrieve($sessionId);
        } catch (InvalidRequestException) {
            return null;
        }

        return [
            'paid' => $session->payment_status === 'paid',
            'payment_id' => $session->payment_intent,
        ];
    }

    public function getGatewayName(): string
    {
        return 'stripe';
    }
}
