<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScoutOpenSearchSetupTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scout:open-search-setup-templates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates or updates the index templates required for the OpenSearch application.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting the setup for the images index template...');

        $successForAllTemplates = $this->createTemplateOnEngine(
            'images_template',
            [
                'index_patterns' => ['images_index'],
                'template' => [
                    'settings' => [
                        'number_of_shards' => 1,
                        'number_of_replicas' => 1,
                    ],
                    'mappings' => [
                        'properties' => [
                            'id'         => ['type' => 'long'],
                            'user_id'    => ['type' => 'long'],
                            'title'      => ['type' => 'text'],
                            'description'      => ['type' => 'text'],
                            'status'     => ['type' => 'keyword'],
                            'tag_ids'    => ['type' => 'keyword'],
                            'tag_names'    => ['type' => 'keyword'],
                            'created_at' => [
                                'type'   => 'date',
                                'format' => 'epoch_second',
                            ],
                            '__class_name' => ['type' => 'keyword'],
                        ],
                    ],
                ],
            ]
        );

        $successForAllTemplates = $this->createTemplateOnEngine(
            'tags_template',
            [
                'index_patterns' => ['tags_index'],
                'template' => [
                    'settings' => [
                        'number_of_shards' => 1,
                        'number_of_replicas' => 1,
                    ],
                    'mappings' => [
                        'properties' => [
                            'id'         => ['type' => 'long'],
                            'name'      => ['type' => 'search_as_you_type'],
                            '__class_name' => ['type' => 'keyword'],
                        ],
                    ],
                ],
            ]
        );

        return $successForAllTemplates;
    }

    protected function createTemplateOnEngine(string $templateName, array $templateDefinition): int
    {
        try {
            $host = config('scout.opensearch.host') ?? 'http://localhost:9200';
            $username = config('scout.opensearch.user');
            $password = config('scout.opensearch.pass');


            $endpoint = rtrim($host, '/') . '/_index_template/' . $templateName;

            $request = Http::asJson()->acceptJson();
            if ($username && $password) {
                $request->withBasicAuth($username, $password);
            }

            $response = $request->put($endpoint, $templateDefinition);

            if ($response->successful()) {
                $this->info("Index template '{$templateName}' was created/updated successfully!");
                return self::SUCCESS;
            }

            $this->error("Failed to create the index template '{$templateName}'.");
            $this->line("Status: " . $response->status());
            $this->line("Response: " . $response->body());
            Log::error('Failed to create OpenSearch template', ['response' => $response->json()]);
            return self::FAILURE;

        } catch (\Exception $e) {
            $this->error('An exception occurred while trying to connect to OpenSearch.');
            $this->line('Error message: ' . $e->getMessage());
            Log::error('Exception during OpenSearch template creation', ['exception' => $e]);
            return self::FAILURE;
        }
    }
}
