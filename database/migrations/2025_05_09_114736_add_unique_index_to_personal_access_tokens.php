<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueIndexToPersonalAccessTokens extends Migration
{
    public function up(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->unique(
                ['tokenable_type', 'tokenable_id', 'name'],
                'personal_access_tokens_user_name_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropUnique('personal_access_tokens_user_name_unique');
        });
    }
}
