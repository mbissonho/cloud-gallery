<?php

use App\Models\ImageStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->string('title', 255)
                ->after('id')
                ->nullable(false);

            $table->string('storage_bucket', 50)
                ->after('title')
                ->nullable(false);

            $table->string('thumbnail_storage_bucket', 50)
                ->after('storage_bucket')
                ->nullable();

            $table->string('storage_key', 50)
                ->after('thumbnail_storage_bucket')
                ->nullable(false);

            $table->enum('status', array_column(ImageStatus::cases(), 'value'))
                ->after('storage_key')
                ->nullable(false)
                ->default(ImageStatus::PROCESSING->value);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->dropColumn('status');
            $table->dropColumn('storage_bucket');
            $table->dropColumn('thumbnail_storage_bucket');
            $table->dropColumn('storage_key');
        });
    }
};
