<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 团队成员的角色（团队空间）。
 *
 * v2 只有 power（0 owner / 1 manager / 2 member）且只会插入、从不修改；v3 新增
 * editor（只能改、不能建删）与 reader（只读），v2 表达不了，所以另起一列。
 *
 * 不给默认值：v2 建团队时写入的创建者行（power 0）也会套上默认值，变成只读。
 * 为空时按 power 推导，见 GroupMember::effectiveRole()。
 * v4 下线后：按 power 回填空值、改 NOT NULL、删掉那段推导。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_members', function (Blueprint $table) {
            $table->string('role', 16)->nullable()
                ->comment('团队角色 owner/manager/editor/reader；为空按 power 推导（v2 写入的行，v4 下线后回填并改 NOT NULL）');
        });
    }

    public function down(): void
    {
        Schema::table('group_members', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
