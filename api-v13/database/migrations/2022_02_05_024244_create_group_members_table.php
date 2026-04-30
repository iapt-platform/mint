<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('group_members', function (Blueprint $table) {
            #使用雪花id
            $table->bigInteger('id')->primary();

            $table->string('user_id', 36)->index();
            $table->string('group_id', 36)->index();
            $table->string('group_name', 64)->nullable();

            $table->integer('power')->default(1);
            $table->integer('level')->default(0);
            $table->integer('status')->default(1)->index();
            $table->timestamp('created_at')->useCurrent()->index();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->index();

            $table->unique(['user_id', 'group_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('group_members');
    }
};
