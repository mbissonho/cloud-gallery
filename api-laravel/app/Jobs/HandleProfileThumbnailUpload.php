<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class HandleProfileThumbnailUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $data)
    {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $bucketName = $this->data['bucket'];
        $objectKey = $this->data['key'];

        Log::info("Processing profile thumbnail created in bucket: [{$bucketName}], key: [{$objectKey}]");

        $storageKeyUuid = explode('.', $objectKey)[0] ?? null;

        $user = User::query()->where('new_photo_storage_hash', $storageKeyUuid)->get()->first();

        if(!$user) return;

        $user->update([
            'photo_storage_key' => $objectKey,
            'new_photo_storage_hash' => ''
        ]);
    }
}
