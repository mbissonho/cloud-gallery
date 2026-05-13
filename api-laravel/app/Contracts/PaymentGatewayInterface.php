<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Create a checkout session for a one-time image purchase.
     *
     * @param array{
     *     image_title: string,
     *     amount_cents: int,
     *     currency: string,
     *     customer_email: string|null,
     *     success_url: string,
     *     cancel_url: string,
     *     metadata: array<string, string>,
     *     locale?: string|null,
     * } $params
     * @return array{session_id: string, checkout_url: string}
     */
    public function createCheckoutSession(array $params): array;

    /**
     * Parse and validate the incoming webhook payload.
     *
     * @return array{event_type: string, session_id: string, payment_id: string}|null
     */
    public function parseWebhookPayload(string $payload, string $signature): ?array;

    /**
     * Look up the current payment status of a previously-created checkout
     * session by calling the gateway directly. Used as a fall-forward on the
     * success page when the async webhook hasn't arrived yet (sandbox without
     * stripe-cli forward, slow webhooks, transient delivery failures).
     *
     * @return array{paid: bool, payment_id: string|null}|null
     *     null when the session id is unknown to the gateway.
     */
    public function verifySessionPayment(string $sessionId): ?array;

    public function getGatewayName(): string;
}
