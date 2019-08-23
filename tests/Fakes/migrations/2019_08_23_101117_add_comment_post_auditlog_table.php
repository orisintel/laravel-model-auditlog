<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommentPostAuditLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comment_post_auditlog', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('comment_id')->index();
            $table->unsignedInteger('post_id')->index();
            $table->unsignedTinyInteger('event_type')->index();
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->string('field_name')->index();
            $table->text('field_value_old')->nullable();
            $table->text('field_value_new')->nullable();
            $table->timestamp('occurred_at')->index()->default('CURRENT_TIMESTAMP');

            $table->foreign(['comment_id','post_id'])
                ->references(['comment_id','post_id'])
                ->on('comment_post');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comment_post_auditlog');
    }
}
