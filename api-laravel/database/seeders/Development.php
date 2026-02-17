<?php

namespace Database\Seeders;

use Database\Seeders\Development\JohnDoeUserWithImagesAndTags;
use Illuminate\Database\Seeder;

class Development extends Seeder
{
    public function run(): void
    {
        $this->call(JohnDoeUserWithImagesAndTags::class);
    }
}
