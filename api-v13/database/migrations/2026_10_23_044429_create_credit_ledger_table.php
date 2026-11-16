<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 积分流水：唯一的账本。余额由流水汇总，不另存。
 *
 * - bucket=free：每日赠送池的消耗与退回（赠送本身不入账，用 day 汇总当天用了多少）；
 * - bucket=earned：赚来的积分（earn）、提问消耗（spend）、退回（refund）、手工调整（adjust）。
 *
 * day 是这条流水归属的 UTC 日：消耗记当天，退回沿用原消耗的那天，赚取记活动发生的那天。
 * 唯一键 (account_id, account_type, source, ref, kind, bucket) 让扣费、退款、结算都能安全重放。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_ledger', function (Blueprint $table) {
            $table->id();
            $table->string('account_type', 8)->comment('账号类型：user（登录用户）/ guest（游客）');
            $table->uuid('account_id')->comment('账号 id：登录用户 uuid 或游客设备匿名 uuid；必须与 account_type 一起用（设备 id 由客户端填，可能与某个用户 uid 相同）');
            $table->integer('delta')->comment('正数入账，负数扣减');
            $table->string('kind', 16)->comment('earn / spend / refund / adjust');
            $table->string('bucket', 16)->comment('free / earned');
            $table->string('source', 32)->comment('reading / editing / agent_run / admin');
            $table->string('ref', 128)->comment('run_id、日期或调整单号');
            $table->date('day')->comment('归属的 UTC 日');
            $table->unsignedInteger('policy_version')->comment('记账时生效的积分策略版本（config mint.credits.policy.version，生效日 YYYYMMDD）');
            $table->ipAddress('client_ip')->nullable()->comment('游客消耗时的客户端 IP，用于同 IP 每日上限');
            $table->string('note')->nullable()->comment('备注');
            $table->timestamps();

            $table->unique(['account_id', 'account_type', 'source', 'ref', 'kind', 'bucket']);
            $table->index(['account_id', 'account_type', 'bucket', 'day']);
            $table->index(['client_ip', 'day']);
            $table->index(['source', 'day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_ledger');
    }
};
