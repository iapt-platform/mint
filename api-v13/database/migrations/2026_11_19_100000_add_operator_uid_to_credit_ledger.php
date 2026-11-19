<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 积分流水的操作人：管理后台手工加减积分（kind=adjust、source=admin）时记下是哪位管理员。
 *
 * 其余流水（结算、扣费、退回）由系统产生，留空；artisan credits:adjust 在命令行执行，也留空。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('credit_ledger', function (Blueprint $table) {
            $table->uuid('operator_uid')->nullable()
                ->comment('操作人用户 uuid（user_infos.userid）：管理后台手工调整时填写；系统产生或命令行调整的流水为空');
        });
    }

    public function down(): void
    {
        Schema::table('credit_ledger', function (Blueprint $table) {
            $table->dropColumn('operator_uid');
        });
    }
};
