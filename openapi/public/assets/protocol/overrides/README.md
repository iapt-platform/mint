# 手写补充

`resources/auto/` 全部由 `openapi/scripts/generate.php` 自动生成，重跑会覆盖。

**首选做法不是在这里写 YAML**，而是把参数说明写进控制器的文档注释
（`@queryParam` / `@bodyParam` / `@urlParam`，见 `openapi/README.md`），
那样说明离代码最近，改接口时不容易忘记同步。

这里只放不适合写进 PHP 注释的东西：长篇示例响应、跨接口的补充说明等。
建同名文件（例如 `v2-channel.yaml`），内容按 OpenAPI Path Item 结构写，
生成时深度合并到自动结果之上。

合并规则：同名键以 overrides 为准，**数组整体替换**。所以在这里写 `parameters`
会覆盖掉自动生成的全部参数，通常不是你想要的。
