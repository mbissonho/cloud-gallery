<?php

namespace App\Console\Commands\Queue;

use App\Jobs\HandleProfileThumbnailUpload;
use Aws\Sqs\SqsClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Command\Command as CommandAlias;

class ConsumeProfileThumbnailQueue extends Command
{
    protected $signature = 'queue:consume-s3-profile-thumbnail-upload-events';

    protected $description = 'Command to process raw S3 profile thumbnail creation events';

    public function handle(SqsClient $sqsClient): int
    {
        $queueUrl = config('queue.connections.sqs.prefix') . '/' . config('queue.profile_thumbnail_queue');
        $this->info("[*] Waiting for messages on [$queueUrl]. To exit press CTRL+C");

        try {
            $result = $sqsClient->receiveMessage([
                'QueueUrl' => $queueUrl,
                'MaxNumberOfMessages' => 10,
                'WaitTimeSeconds' => 20
            ]);

            $messages = $result->get('Messages') ?? [];

            foreach ($messages as $message) {
                $receiptHandle = $message['ReceiptHandle'];
                $body = json_decode($message['Body'], true);

                $this->info("Message received. Trying to dispatch job.");

                $s3Info = $body['Records'][0] ?? null;
                if ($s3Info) {
                    $jobData = [
                        'bucket' => $s3Info['s3']['bucket']['name'],
                        'key' => urldecode($s3Info['s3']['object']['key']),
                    ];

                    $this->info("Message received. Dispatching job...");

                    HandleProfileThumbnailUpload::dispatch($jobData)->onQueue('default');

                    $this->info("Job dispatched for key: {$jobData['key']}");

                    $sqsClient->deleteMessage([
                        'QueueUrl' => $queueUrl,
                        'ReceiptHandle' => $receiptHandle,
                    ]);

                    $this->info("Raw message deleted from queue.");

                } else {
                    Log::warning('Received SQS message without valid S3 records.', $body);
                    $sqsClient->deleteMessage(['QueueUrl' => $queueUrl, 'ReceiptHandle' => $receiptHandle]);
                }
            }

            return CommandAlias::SUCCESS;

        } catch (\Throwable $e) {
            Log::error('Error processing SQS message: ' . $e->getMessage());
            return CommandAlias::FAILURE;
        }
    }
}
