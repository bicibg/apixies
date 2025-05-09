<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDeletesToPersonalAccessTokensTable extends Migration
{
    public function up()
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->softDeletes(); // adds a nullable deleted_at column
        });
    }

    public function down()
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
