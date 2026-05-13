<?php

namespace App\Models;

use App\Mail\DownloadLinkMail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class Purchase extends Model
{
    protected $fillable = [
        'image_id',
        'user_id',
        'buyer_email',
        'amount_cents',
        'currency',
        'gateway',
        'gateway_session_id',
        'gateway_payment_id',
        'status',
        'download_token',
        'download_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PurchaseStatus::class,
            'download_expires_at' => 'datetime',
            'amount_cents' => 'integer',
        ];
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function generateDownloadToken(): string
    {
        return Str::random(64);
    }

    public function isDownloadable(): bool
    {
        return $this->status === PurchaseStatus::COMPLETED
            && $this->download_expires_at
            && $this->download_expires_at->isFuture();
    }

    public function markCompleted(string $paymentId): void
    {
        $this->update([
            'gateway_payment_id' => $paymentId,
            'status' => PurchaseStatus::COMPLETED,
            'download_expires_at' => now()->addHours(
                config('checkout.download_expiry_hours')
            ),
        ]);

        // Queued on the default queue (database in dev, redis under terraform/ECS).
        // DownloadLinkMail implements ShouldQueue, so it never blocks the webhook
        // response or the success-page sync.
        Mail::to($this->buyer_email)->queue(new DownloadLinkMail($this));
    }
}
