# 服务器端测试和运行的项目

| 序列号 | 项目              | 代码完成 | 功能测试 | 可重入 | 运行中 | 结束 |
| ------ | ----------------- | -------- | -------- | ------ | ------ | ---- |
| 1      | 三藏全文搜索      | ✅       | ✅       | ✅     | ✅     | ✅   |
| 2      | 百科全文搜索      | ✅       |          |        |        |      |
| 3      | 注疏穿插          | ✅       | ✅       | ✅     | ✅     |      |
| 4      | 汉译 nissaya      | ✅       | ✅       | ✅     |        |      |
| 5      | ai 译文(deepseek) | ✅       |          |        |        |      |
| 6      | ai 译文(claude)   |          |          |        |        |      |
| 7      | 第三方译文导入    | ✅       |          |        |        |      |
| 8      | 五大册-AI 汉译    | ✅       |          |        |        |      |
| 9      | AI 百科           | ✅       |          |        |        |      |
| 10     | AI wbw            |          |          |        |        |      |

> 功能测试 是指在wikipali server上功能测试通过

```bash
# 注疏穿插
php artisan upgrade:sys.commentary --model=a31d2036-2643-485c-8f3a-3471800cbb72 --thinking=false --skip='abhi*'

# 汉译 nissaya
php artisan upgrade:ai.translation nissaya 93cd528e-6539-11f0-808a-1f00186e84a7 --model=14c61827-54e7-463a-94eb-c06b538e199f

# 五大册-AI 汉译
php artisan app:ai-article-translate --anthology=22ae16b4-68b3-4403-b155-ede40c509c7e --model=a31d2036-2643-485c-8f3a-3471800cbb72 --channel=877ae5ee-4a92-11f0-808a-6b8433850072
```
