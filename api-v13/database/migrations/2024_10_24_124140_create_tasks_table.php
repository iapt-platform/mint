<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 标题 	手输文本
     * 类型 	固定标签 project / 翻译主笔
     * 描述 	任务内容文本
     * 特别指派 	用户ID 	多个，选填
     * 角色 	角色标签 	与特别指派互斥
     * 实际执行人 	用户ID 	单个
     * 实际执行人关联任务 	ID 	与所关联的任务实际执行人相同
     * 工程 	ID 	单个
     * //工程路径 json  这个任务所属的project 的路径 studio/project1/project2
     * 前置任务ID 		选填
     * 状态 	列表  待定	待发布、待领取、进行中、待审核、重做、通过、完结
     * 拥有者 	用户ID 	单个
     * 修改者 	用户ID 	单个
     * 里程碑   uuid 	单个
     * 建立日期
     * @return void
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v1mc()'));
            $table->string('title', 512)->index();
            $table->string('type', 32)->index()->default('project');
            $table->string('category', 256)->nullable()->index()->comment('类别: 翻译，审稿，百科 等');
            $table->text('summary')->nullable();
            $table->text('description')->nullable();
            $table->integer('weight')->index()->default(0)->comment('权重');
            $table->integer('progress')->index()->default(0)->comment('进度0-100');
            $table->uuid('parent_id')->index()->nullable();
            $table->jsonb('assignees_id')->index()->nullable()->comment('责任人');
            $table->jsonb('roles')->index()->nullable()->comment('对于领取任务的人的角色要求');
            $table->uuid('executor_id')->index()->nullable()->comment('实际执行人');
            $table->uuid('executor_relation_task_id')->index()->nullable();
            $table->uuid('project_id')->index();
            $table->boolean('is_milestone')->index()->default(false)->comment('是否是里程碑');
            $table->uuid('owner_id')->index()->comment('任务拥有者:用户或者team-space');
            $table->uuid('creator_id')->index();
            $table->uuid('editor_id')->index();
            $table->integer('order')->index()->default(1)->comment('拖拽排序顺序');
            $table->string('status', 32)->index()->default('pending');
            $table->boolean('closed_by_subtask')->default(true);
            $table->timestamp('started_at')->nullable()->index()->comment('实际开始时间');
            $table->timestamp('finished_at')->nullable()->index()->comment('实际结束时间');
            $table->timestamp('begin_at')->nullable()->index()->comment('计划开始时间');
            $table->timestamp('end_at')->nullable()->index()->comment('计划结束时间');
            $table->boolean('plan_with_time')->index()->default(false)->comment('计划时间包含时间');
            $table->boolean('hide_description_before_begin')->index()->default(false)->comment('在开始时间之前隐藏描述');
            $table->text('script')->nullable()->comment('lua脚本');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
