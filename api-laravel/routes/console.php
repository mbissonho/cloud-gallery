<?php

use App\Console\Commands\Queue\ConsumeProfileThumbnailQueue;
use App\Console\Commands\Queue\ConsumeThumbnailQueue;
use Illuminate\Support\Facades\Schedule;

Schedule::command(ConsumeThumbnailQueue::class)->everyMinute();
Schedule::command(ConsumeProfileThumbnailQueue::class)->everyFourMinutes();


