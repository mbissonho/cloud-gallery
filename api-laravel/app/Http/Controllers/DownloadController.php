<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Purchase;
use App\Models\PurchaseStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function __construct(
        private readonly PaymentGatewayInterface $gateway
    ) {}

    public function __invoke(string $token): JsonResponse
    {
        $purchase = Purchase::where('download_token', $token)->firstOrFail();

        // Stripe's browser redirect races the server-to-server webhook: in
        // sandbox without `stripe listen` the webhook never arrives at all,
        // and in prod it can lag a few seconds. Reconcile against the gateway
        // before deciding the link is unavailable.
        if ($purchase->status === PurchaseStatus::PENDING) {
            $verification = $this->gateway->verifySessionPayment($purchase->gateway_session_id);

            if ($verification && $verification['paid']) {
                $purchase->markCompleted($verification['payment_id']);
            }
        }

        if (!$purchase->isDownloadable()) {
            return response()->json([
                'message' => trans('checkout.download_unavailable'),
            ], 403);
        }

        $image = $purchase->image;
        $extension = pathinfo($image->storage_key, PATHINFO_EXTENSION);
        $filename = $image->title . '.' . $extension;

        // Strip characters that would break the Content-Disposition header.
        // Image titles come from user input, so sanitize before echoing into a header.
        $safeFilename = preg_replace('/[\r\n"]/', '', $filename);

        // ResponseContentDisposition is forwarded into the presigned URL as
        // response-content-disposition; S3 echoes the header back on GET, which
        // is what makes the browser download instead of rendering the image inline.
        // The download attribute on the SPA's <a> is ignored cross-origin, so this
        // is the only reliable way to force "save as".
        $temporaryUrl = Storage::disk('main-image')->temporaryUrl(
            $image->storage_key,
            now()->addMinutes(15),
            [
                'ResponseContentDisposition' => 'attachment; filename="' . $safeFilename . '"',
            ]
        );

        return response()->json([
            'download_url' => $temporaryUrl,
            'filename' => $filename,
            'expires_in_minutes' => 15,
        ]);
    }
}
