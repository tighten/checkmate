<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrivacyToProjectsTable extends Migration
{
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('is_private')->default(true);
        });
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('is_private');
        });
    }
}
