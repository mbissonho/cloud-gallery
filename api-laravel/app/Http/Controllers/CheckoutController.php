<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentGatewayInterface;
use App\Http\Requests\CreateCheckoutSessionRequest;
use App\Models\Image;
use App\Models\ImageStatus;
use App\Models\Purchase;
use App\Models\PurchaseStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly PaymentGatewayInterface $gateway
    ) {}

    public function createSession(CreateCheckoutSessionRequest $request): JsonResponse
    {
        $image = Image::where('status', ImageStatus::AVAILABLE)
            ->findOrFail($request->validated('image_id'));

        $user = Auth::guard('sanctum')->user();

        // Owners can already access their own full-resolution image via the
        // edit/manage flow — charging them for it would be nonsensical.
        if ($user && $user->id === $image->user_id) {
            return response()->json([
                'message' => trans('checkout.cannot_buy_own_image'),
            ], 403);
        }

        $email = $user?->email ?? $request->validated('email');

        if (!$email) {
            return response()->json([
                'message' => trans('checkout.guest_email_required'),
            ], 422);
        }

        $amountCents = $image->price_cents ?: config('checkout.default_price_cents');
        $downloadToken = Purchase::generateDownloadToken();

        $frontendUrl = config('checkout.frontend_url');
        $locale = app()->getLocale();

        $result = $this->gateway->createCheckoutSession([
            'image_title' => trans('checkout.product_title', ['title' => $image->title]),
            'amount_cents' => $amountCents,
            'currency' => config('checkout.currency'),
            'customer_email' => $email,
            'success_url' => $frontendUrl . '/checkout/success?token=' . $downloadToken,
            'cancel_url' => $frontendUrl . '/view/' . $image->id,
            'metadata' => [
                'image_id' => (string) $image->id,
                'download_token' => $downloadToken,
            ],
            'locale' => $locale,
        ]);

        Purchase::create([
            'image_id' => $image->id,
            'user_id' => $user?->id,
            'buyer_email' => $email,
            'amount_cents' => $amountCents,
            'currency' => config('checkout.currency'),
            'gateway' => $this->gateway->getGatewayName(),
            'gateway_session_id' => $result['session_id'],
            'download_token' => $downloadToken,
        ]);

        return response()->json([
            'checkout_url' => $result['checkout_url'],
        ]);
    }

    public function handleWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature', '');

        $event = $this->gateway->parseWebhookPayload($payload, $signature);

        if (!$event) {
            return response()->json(['message' => trans('checkout.invalid_webhook')], 400);
        }

        if ($event['event_type'] === 'checkout.completed') {
            $purchase = Purchase::where('gateway_session_id', $event['session_id'])->first();

            // Skip if the success-page sync already flipped the status — keeps the
            // download email idempotent across webhook + fall-forward verification.
            if ($purchase && $purchase->status === PurchaseStatus::PENDING) {
                $purchase->markCompleted($event['payment_id']);
            }
        }

        return response()->json(['message' => 'ok']);
    }
}
