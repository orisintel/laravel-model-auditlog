<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPostsAuditLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts_auditlog', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('subject_id')->index();
            $table->unsignedTinyInteger('event_type')->index();
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->string('field_name')->index();
            $table->text('field_value_old')->nullable();
            $table->text('field_value_new')->nullable();
            $table->timestamp('occurred_at')->index()->default('CURRENT_TIMESTAMP');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts_auditlog');
    }
}
