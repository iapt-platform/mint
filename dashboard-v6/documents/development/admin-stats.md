# 管理后台统计：口径与实现

> `/admin/stats/reading`（阅读统计）与 `/admin/stats/ai`（AI 对话统计），仅 root / administrator 可见。
> 后端：api-v13 `GET /api/v3/admin/reading-stats`、`/agent-stats`、`/agent-runs`、`/agent-runs/{run}`
> （`App\Services\V3\ReadingStatsService`、`AgentStatsService`，中间件 `auth.v3` + `auth.v3.admin`）。
> 前端：`src/api/adminStats.ts` → `src/hooks/useAdminStats.ts` → `src/features/admin-stats/*` → `src/pages/admin/stats/*`。

## 共同约定

- 时间范围是 **UTC 日** 的闭区间 `[from, to]`，缺省近 30 天，最长 366 天。
- 每个指标都带「上一同长区间」的值（`previous`），前端算环比；上一区间是 0 时不显示环比。
- 统计结果在后端缓存 10 分钟；提问列表与研究过程不缓存。
- 每张卡片下方写数据来源表名，改口径时同步改卡片。

## 阅读统计

| 指标 | 口径 | 来源 |
|---|---|---|
| 活跃读者 | 区间内阅读记录有更新的去重**登录**用户。游客的阅读服务器看不到 | `recents` |
| 日活 / 月活 | 区间最后一天的活跃读者 / 截至那天的 30 天活跃读者 | `recents` |
| 打开章节 | 区间内有更新的阅读记录数。recents 按（用户, 章节）upsert，只记最后一次，所以不是逐次打开数 | `recents` |
| 读经时长 | App 阅读器在前台、且 3 分钟内有翻读动作才计时；**Web 没有计时** | `reading_dailies` |
| 章节浏览 | `views` 在区间内有更新的行（访客 × 章节），**含游客与搜索引擎爬虫**，只看趋势 | `views` |
| 查词 | 登录用户经 v2 接口的 dict_lookup 请求 | `user_operation_logs` |
| 热门章节 | 按去重读者数排序的前 10 个章节 | `recents` + `pali_texts` |
| 三藏 / 根本·义注·复注分布 | 阅读记录按书归类；书的类别取挂在 `pali_texts` 章节行上的标签（sutta / vinaya / abhidhamma / añña，mūla / aṭṭhakathā / ṭīkā） | `recents` + `tag_maps` |
| 读经时长分布 | 每人每天的读经秒数分桶 | `reading_dailies` |
| 读经积分 | 发放总额、获积分人数、达每日上限的人·天、服务端按墙钟核减掉的比例 | `credit_ledger`、`reading_dailies` |

**客户端区分**：`recents.param` 里 `client = mobile`（mobile 从 2026-10 起写）或 `mode = reading`（mobile 的老写法）算 App，其余算 Web
（v4 网页写 `read` / `edit` / `wbw`）。

## AI 对话统计

| 指标 | 口径 | 来源 |
|---|---|---|
| 提问数 | 区间内开始的提问（`started_at`） | `agent_runs` |
| 提问的人 | 登录用户按账号、游客按设备匿名 id 去重 | `agent_threads.account_type` + `account_id` |
| 响应耗时 | 成功（status=ok）提问的 `latency_ms` P50 / P95 | `agent_runs` |
| 拦截 / 出错 | status = denied（额度不足）与 error（已退积分）；另列「未查额度放行」次数（2026-11 起 agent 不再放行，mint 不可用时回网络错误并记 denied；这一项只剩历史数据） | `agent_runs`、`agent_run_steps`（event · quota） |
| 每日提问 | 按研究路径（question / term / passage / chat）堆叠 | `agent_runs.route` |
| 工具调用 Top | 次数、出错数、平均耗时 | `agent_run_steps`（kind = tool） |
| 积分流水 | 赠送 / 积分消耗、出错退回、读经 / 编辑发放、手工调整 | `credit_ledger` |
| token / 可用于改进 | token 合计；`improve = true` 的提问占比 | `agent_runs` |
| 用户评价 | 点赞 / 点踩数（按评价的 `created_at`），评价覆盖率＝评价数 ÷ 提问数；点踩原因分布 | `agent_feedback` |

**提问记录**：列表与研究过程（回答、证据全文、计划、全部步骤）对 root / administrator 都可见；游客只显示设备匿名 id 与 IP。
回答与证据按纯文本显示，不渲染 HTML。

**用户评价审阅**（与提问记录同一张卡片的第二个页签）：最新的在前，可按赞 / 踩、标签或原因筛；每条带对应提问和
路由，点一行打开那次提问的研究过程。`run_id` 为空表示评价先到、提问还没入库。不显示账号身份。

后台不做回答质量评估：要评估就用 wikipali-plugins 的 `wikipali admin digest --full` 把回答与证据拉给 agent 评（agent-poc `improve-answers` 技能）。

## 索引

`2026_10_24_*_add_admin_stats_indexes`：已有表 `user_operation_logs (op_type, created_at)`、`views (updated_at)`，
普通的事务内建索引。`agent_runs` 是新表，`started_at`、`assistant_message_id` 的索引直接写在它的 create 迁移里。

## 以后可以加

- Web 端读经计时（目前只有 App）。
- 阅读的次日 / 7 日留存（需要逐次阅读事件，recents 只记最后一次，做不了）。
