<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * discussions 加 content_type_v2：v3 写入的正文方言（`markdown-e2`）。
 *
 * 原 content_type 是带 CHECK 约束的枚举（markdown / text / html），存不下 markdown-e2；
 * 改约束等于改列类型（铁律 4），所以另加一列。v3 写入时这一列存 markdown-e2、content_type 仍填 markdown
 * （v2 / v4 照旧读 content_type）；v3 读取时这一列优先，为空取 content_type。
 *
 * v2 下线后 content_type 废弃，只认这一列（届时把存量的空值回填为 markdown，再删 content_type）。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discussions', function (Blueprint $table) {
            $table->string('content_type_v2', 16)->nullable()
                ->comment('v3 写入的正文方言（markdown-e2）；为空时以 content_type 为准。v2 不读这一列');
        });
    }

    public function down(): void
    {
        Schema::table('discussions', function (Blueprint $table) {
            $table->dropColumn('content_type_v2');
        });
    }
};
