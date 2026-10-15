<?php

/**
 * api-test 本机配置模板。复制成 config.local.php 再填（已 gitignore，不要提交）：
 *
 *   cp config.local.example.php config.local.php
 *
 * 优先级：命令行参数 > 环境变量（API_TEST_*）> 本文件 > config.php 的默认值。
 * 不需要的键删掉或留 null 即可。
 */

return [
    // 默认服务器 alias（见 config.php 的 servers），或完整 base URL
    'default_server' => 'local',

    // 测试账号：调 POST /v3/sessions 换 token。也可以直接给 token（优先）
    'username' => 'test161',
    'password' => null,
    'token' => null,

    // 覆盖 fixtures.php 里的种子数据（换环境时用），只写要改的键
    'fixtures' => [
        // 讨论写用例挂的句子：必须在测试账号自己的 channel 里、由测试账号编辑过
        // 'discussion_sentence' => 'ee5259be-5d1e-4408-a2a6-566a5773eef1',
        // 'discussion_channel' => '1721b7fa-e471-46fd-b860-64b9e79d370e',
        // agent 代写用例：归测试账号所有的 AI 模型 uid；不给则 SKIP
        // 'agent_model' => null,
        // email-certifications「已注册邮箱」用例；不给则 SKIP
        // 'registered_email' => 'someone@example.com',
    ],
];
