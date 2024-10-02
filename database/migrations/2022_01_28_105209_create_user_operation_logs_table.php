<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserOperationLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_operation_logs', function (Blueprint $table) {
			#使用雪花id
            $table->bigInteger('id')->primary();
		    $table->bigInteger('user_id')->index();
		    $table->bigInteger('op_type_id')->index();
            $table->enum('op_type',['channel_update',
                                    'channel_create',
                                    'article_update',
                                    'article_create',
                                    'dict_lookup',
                                    'term_create',
                                    'term_update',
                                    'term_lookup',
                                    'wbw_update',
                                    'wbw_create',
                                    'sent_update',
                                    'sent_create',
                                    'collection_update',
                                    'collection_create',
                                    'nissaya_open'
                                    ]);
            $table->text('content')->nullable();
		    $table->bigInteger('timezone')->default(0);
		    $table->bigInteger('create_time');
			$table->timestamp('created_at')->useCurrent();

            $table->index(['user_id','op_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_operation_logs');
    }
}
