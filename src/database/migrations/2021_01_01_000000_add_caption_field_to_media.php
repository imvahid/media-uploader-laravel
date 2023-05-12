<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCaptionFieldToMedia extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('media')) {
            Schema::table('media', function (Blueprint $table) {
                if (!Schema::hasColumn('media', 'caption')) {
                    $table->string('caption', 512)->nullable()->after('size');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('media')) {
            Schema::table('media', function (Blueprint $table) {
                $table->dropColumn('caption');
            });
        }
    }
}
