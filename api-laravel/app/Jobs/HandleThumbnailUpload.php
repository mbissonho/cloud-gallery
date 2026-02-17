<?php

namespace App\Jobs;

use App\Models\Image;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class HandleThumbnailUpload implements ShouldQueue
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

        Log::info("Processing thumbnail created in bucket: [{$bucketName}], key: [{$objectKey}]");

        $image = Image::query()->where('storage_key', $objectKey)->get()->first();

        if(!$image) return;
        /* @var Image $image */
        $image->turnAvailable();
    }
}
