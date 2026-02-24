# 🛠️ Ant Design v4 → v6 迁移工具使用指南

本工具包包含 3 个辅助脚本和 1 个详细检查清单，帮助你高效完成迁移工作。

---

## 📦 工具包内容

```
migration-tools/
├── migration-checklist.md    # 📋 迁移检查清单（主文档）
├── scan-api-diff.js          # 🔍 API 差异扫描器
├── import-replace.js         # 🔧 批量替换工具
├── component-template.js     # 📝 组件模板生成器
└── USAGE.md                  # 📖 本使用指南
```

---

## 🚀 快速开始

### Step 1: 扫描旧项目代码

先了解你的代码中有哪些需要修改的地方：

```bash
# 扫描旧项目代码，生成报告
node scan-api-diff.js /path/to/old-project/src

# 导出详细的 JSON 报告（可选）
node scan-api-diff.js /path/to/old-project/src --json=scan-report.json
```

**输出示例：**
```
📊 Ant Design v4 → v6 API 差异扫描报告
===============================================================================

📁 扫描统计:
   总文件数: 581
   已扫描: 581
   有问题的文件: 127

🚨 问题统计:
   🔴 Critical: 5 个
   🟠 High: 43 个
   🟡 Medium: 89 个
   🟢 Low: 12 个

📋 详细问题列表:
   ...
```

### Step 2: 批量替换简单的 API

对于可以自动替换的 API（如 visible → open），使用批量替换工具：

```bash
# 预览将要修改的内容（强烈推荐先预览）
node import-replace.js /path/to/new-project/src --dry-run

# 确认无误后，执行替换并备份
node import-replace.js /path/to/new-project/src --backup

# 如果你很确定，可以直接替换（不推荐）
node import-replace.js /path/to/new-project/src
```

**该工具会自动处理：**
- ✅ `visible` → `open`
- ✅ `onVisibleChange` → `onOpenChange`
- ✅ `moment` → `dayjs`
- ✅ `overlay` → `menu` (Dropdown)
- ✅ `BackTop` → `FloatButton.BackTop`

### Step 3: 生成新组件模板

在新项目中创建符合 v6 规范的组件：

```bash
# 生成基础组件
node component-template.js basic UserCard ./src/components/user

# 生成表单组件
node component-template.js form UserForm ./src/components/user

# 生成 ProTable 组件
node component-template.js proTable UserList ./src/components/user

# 生成完整的主题系统（重要！）
node component-template.js --theme ./src/theme
```

### Step 4: 按照检查清单逐步迁移

打开 `migration-checklist.md`，按照清单逐个完成迁移任务。

---

## 🔍 工具 1: scan-api-diff.js - API 差异扫描器

### 功能
扫描代码中使用的 antd v4 API，标记需要修改的地方。

### 使用场景
- ✅ 开始迁移前，了解代码中有多少需要修改的地方
- ✅ 评估迁移工作量
- ✅ 识别高风险组件
- ✅ 迁移过程中，验证是否有遗漏

### 命令详解

```bash
# 基础用法
node scan-api-diff.js <目标目录>

# 导出 JSON 报告
node scan-api-diff.js <目标目录> --json=<输出文件.json>
```

### 实战示例

```bash
# 示例 1: 扫描整个 src 目录
node scan-api-diff.js ../old-project/src

# 示例 2: 只扫描特定模块
node scan-api-diff.js ../old-project/src/components/general

# 示例 3: 导出报告后用其他工具分析
node scan-api-diff.js ../old-project/src --json=report.json
cat report.json | jq '.issues | length'  # 统计问题数量
```

### 报告解读

报告会按严重程度分类问题：

- 🔴 **Critical** - 必须立即修改，否则代码无法运行
  - 例如：使用了已废弃的 `getFieldDecorator`
  
- 🟠 **High** - 高优先级，会导致功能异常
  - 例如：`visible` 属性不再生效
  
- 🟡 **Medium** - 中优先级，可能导致警告或不推荐的用法
  - 例如：直接调用 `message.success()` 需要包裹 `<App>`
  
- 🟢 **Low** - 低优先级，建议优化
  - 例如：`BackTop` 组件有更好的替代方案

### 输出文件

如果使用 `--json` 参数，会生成 JSON 格式的详细报告：

```json
{
  "timestamp": "2026-02-14T10:30:00.000Z",
  "stats": {
    "totalFiles": 581,
    "scannedFiles": 581,
    "filesWithIssues": 127,
    "issues": {
      "critical": 5,
      "high": 43,
      "medium": 89,
      "low": 12
    }
  },
  "issues": {
    "src/components/general/ThemeSelect.tsx": [
      {
        "line": 42,
        "column": 8,
        "code": "visible={true}",
        "suggestion": "❌ 使用了 visible 属性",
        "fix": "将 visible 改为 open",
        "severity": "high",
        "component": "Modal/Drawer/Popover/Tooltip/Dropdown"
      }
    ]
  }
}
```

---

## 🔧 工具 2: import-replace.js - 批量替换工具

### 功能
自动替换代码中的 antd v4 API 调用。

### 使用场景
- ✅ 批量替换简单的 API 变更（如 visible → open）
- ✅ 替换 moment.js 为 dayjs
- ✅ 更新其他机械性的代码修改

### ⚠️ 重要警告

**该工具会直接修改你的代码文件！**

请务必：
1. 先使用 `--dry-run` 预览
2. 使用 `--backup` 备份原文件
3. 使用 Git 等版本控制系统
4. 在测试分支上操作

### 命令详解

```bash
# 预览模式（强烈推荐先运行）
node import-replace.js <目标目录> --dry-run

# 执行替换并备份
node import-replace.js <目标目录> --backup

# 直接替换（谨慎使用）
node import-replace.js <目标目录>
```

### 实战示例

```bash
# 示例 1: 先预览整个项目的修改
node import-replace.js ./src --dry-run

# 示例 2: 确认无误后执行替换，并备份原文件
node import-replace.js ./src --backup

# 示例 3: 只处理特定目录
node import-replace.js ./src/components/general --backup

# 示例 4: 查看备份文件
find ./src -name "*.bak" | head -5

# 示例 5: 如果替换有问题，恢复备份
find ./src -name "*.bak" -exec bash -c 'mv "$0" "${0%.bak}"' {} \;
```

### 替换规则

目前支持以下自动替换：

| v4 API | v6 API | 说明 |
|--------|--------|------|
| `visible={...}` | `open={...}` | Modal/Drawer/Popover 等 |
| `onVisibleChange={...}` | `onOpenChange={...}` | 同上 |
| `import moment from 'moment'` | `import dayjs from 'dayjs'` | DatePicker 相关 |
| `moment(...)` | `dayjs(...)` | 日期处理函数 |
| `overlay={...}` | `menu={...}` | Dropdown 组件 |
| `<BackTop>` | `<FloatButton.BackTop>` | 返回顶部组件 |

### 注意事项

**⚠️ 以下情况需要手动处理：**

1. **Dropdown 的 menu 属性**
   - 自动替换后，`menu` 需要返回 `Menu` 组件
   - 可能需要调整代码结构

2. **message/notification 静态方法**
   - 不会自动替换（因为需要包裹 `<App>` 组件）
   - 建议迁移到 `App.useApp()` hook

3. **Modal.confirm 等静态方法**
   - 同上，需要使用 `Modal.useModal()` hook

4. **PageHeader 组件**
   - 已从 antd 移除
   - 需要手动迁移到 `@ant-design/pro-components`

---

## 📝 工具 3: component-template.js - 组件模板生成器

### 功能
生成符合 v6 最佳实践的组件模板代码。

### 使用场景
- ✅ 创建新组件时，使用标准模板
- ✅ 重写旧组件时，参考最佳实践
- ✅ 生成主题系统文件

### 可用模板类型

| 类型 | 说明 | 适用场景 |
|------|------|----------|
| `basic` | 基础组件 | 简单的展示组件 |
| `form` | 表单组件 | 包含表单验证、提交逻辑 |
| `modal` | Modal 组件 | 弹窗组件 |
| `table` | Table 组件 | 列表展示、分页 |
| `proTable` | ProTable 组件 | 复杂的表格，带搜索、筛选 |

### 命令详解

```bash
# 生成组件
node component-template.js <类型> <组件名> [输出目录]

# 生成主题系统文件（特殊命令）
node component-template.js --theme [输出目录]

# 查看帮助
node component-template.js --help
```

### 实战示例

```bash
# 示例 1: 生成基础组件
node component-template.js basic UserCard ./src/components/user
# 输出: ./src/components/user/UserCard.tsx

# 示例 2: 生成表单组件
node component-template.js form UserForm ./src/components/user
# 输出: ./src/components/user/UserForm.tsx

# 示例 3: 生成 ProTable 组件
node component-template.js proTable UserList ./src/components/user
# 输出: ./src/components/user/UserList.tsx

# 示例 4: 生成主题系统（重要！）
node component-template.js --theme ./src/theme
# 输出:
#   ./src/theme/ThemeSwitch.tsx   (主题切换器)
#   ./src/theme/AppProvider.tsx   (App 提供者)
#   ./src/theme/tokens.ts         (Design Token 配置)
```

### 主题系统文件说明

使用 `--theme` 命令会生成完整的主题系统：

#### 1. ThemeSwitch.tsx - 主题切换器

```tsx
// 用法示例
import ThemeSwitch from './theme/ThemeSwitch';

<ThemeSwitch onChange={(mode) => {
  console.log('当前主题:', mode); // 'light' | 'dark'
}} />
```

功能：
- ✅ 亮色/暗黑主题切换
- ✅ 本地存储主题偏好
- ✅ 带图标和文字提示

#### 2. AppProvider.tsx - 全局配置提供者

```tsx
// 在根组件使用
import AppProvider from './theme/AppProvider';

function Root() {
  return (
    <AppProvider>
      <YourApp />
    </AppProvider>
  );
}
```

功能：
- ✅ 提供 ConfigProvider 配置
- ✅ 管理主题切换
- ✅ 提供全局 message/modal/notification

#### 3. tokens.ts - Design Token 配置

```tsx
// 自定义主题 Token
export const lightTheme = {
  token: {
    colorPrimary: '#1890ff',
    borderRadius: 6,
    // ...
  },
  components: {
    Button: {
      controlHeight: 32,
    },
  },
};
```

---

## 📋 工具 4: migration-checklist.md - 迁移检查清单

### 功能
详细的分阶段迁移任务清单。

### 使用方法

1. 在你喜欢的 Markdown 编辑器中打开
2. 按照 Phase 顺序完成任务
3. 完成一项勾选一项 `[ ]` → `[x]`
4. 记录遇到的问题和解决方案

### 文档结构

```
📊 迁移进度总览
└── 总体进度追踪

🎯 Phase 1: 基础设施层
└── 类型定义、Redux、路由

🎯 Phase 2: 通用组件层 ⭐ 当前优先级
└── general、auth、api

🎯 Phase 3: 业务组件层
└── 按模块逐个迁移

🎯 Phase 4: 样式和主题系统 ⚠️ 重大变更
└── CSS → Design Token

🎯 Phase 5: 测试和优化
└── 功能测试、性能优化

📚 关键 API 变更速查表
└── 常用组件对照表

🔧 常见问题和解决方案
└── 错误处理指南
```

---

## 🎯 推荐工作流程

### 方案 1: 稳妥型（推荐给大型项目）

```bash
# Step 1: 扫描旧项目，评估工作量
node scan-api-diff.js ../old-project/src --json=scan-report.json

# Step 2: 生成主题系统文件
node component-template.js --theme ./src/theme

# Step 3: 配置根组件使用 AppProvider
# (手动修改 src/index.tsx)

# Step 4: 选择一个简单模块开始迁移（如 general）
# 方式 A: 手动迁移（更安全）
cp -r ../old-project/src/components/general ./src/components/

# 方式 B: 复制后使用替换工具
node import-replace.js ./src/components/general --backup

# Step 5: 测试该模块是否正常工作

# Step 6: 重复 Step 4-5，逐个模块迁移

# Step 7: 最后扫描一次，确保没有遗漏
node scan-api-diff.js ./src
```

### 方案 2: 快速型（推荐给小型项目）

```bash
# Step 1: 直接复制所有代码到新项目
cp -r ../old-project/src/* ./src/

# Step 2: 批量替换简单的 API
node import-replace.js ./src --dry-run  # 先预览
node import-replace.js ./src --backup   # 执行替换

# Step 3: 生成主题系统
node component-template.js --theme ./src/theme

# Step 4: 扫描剩余问题
node scan-api-diff.js ./src

# Step 5: 根据报告手动修复剩余问题

# Step 6: 测试所有功能
```

---

## 🔥 常见问题 FAQ

### Q1: 替换工具会破坏我的代码吗？

**A:** 可能会。所以：
- ✅ 务必先用 `--dry-run` 预览
- ✅ 使用 `--backup` 备份
- ✅ 使用 Git 等版本控制
- ✅ 先在小范围测试

### Q2: 扫描器报告太多问题怎么办？

**A:** 分优先级处理：
1. 先处理 🔴 Critical 和 🟠 High
2. 🟡 Medium 可以逐步处理
3. 🟢 Low 可以最后优化

### Q3: 某些 API 无法自动替换怎么办？

**A:** 手动处理：
- 参考 `migration-checklist.md` 中的 API 对照表
- 查看生成的组件模板作为参考
- 查阅 Ant Design 官方文档

### Q4: 主题系统应该如何集成？

**A:** 三步走：
```bash
# 1. 生成主题文件
node component-template.js --theme ./src/theme

# 2. 在根组件引入
import AppProvider from './theme/AppProvider';

# 3. 包裹你的 App
<AppProvider>
  <App />
</AppProvider>
```

### Q5: ProTable/ProForm 如何迁移？

**A:** 
- ProComponents 也有版本升级
- 使用 `component-template.js proTable` 生成新模板
- 对比新旧代码，逐步调整

### Q6: 样式突然失效怎么办？

**A:** 检查：
- ✅ 是否有覆盖 antd 默认样式的 CSS
- ✅ 类名是否发生变化
- ✅ 是否需要改用 Design Token
- ✅ 是否需要使用 `classNames` API

### Q7: 扫描器漏报或误报怎么办？

**A:** 
- 扫描器基于正则匹配，可能有遗漏
- 建议结合人工检查
- 可以修改 `scan-api-diff.js` 添加新规则

---

## 📚 参考资源

### 官方文档
- [Ant Design v6 官方文档](https://ant.design)
- [v4 → v5 迁移指南](https://ant.design/docs/react/migration-v5)
- [v5 → v6 迁移指南](https://ant.design/docs/react/migration-v6)
- [Design Token 文档](https://ant.design/docs/react/customize-theme)

### 工具资源
- [dayjs 官方文档](https://day.js.org/)
- [ProComponents 文档](https://procomponents.ant.design/)

---

## 💡 最佳实践建议

### 1. 分阶段迁移
不要一次性迁移所有代码，按模块逐个迁移并测试。

### 2. 保持两个版本
在迁移期间，保持 v4 版本可运行，方便对比。

### 3. 及时记录
在 `migration-checklist.md` 中记录遇到的问题和解决方案。

### 4. 代码审查
迁移后的代码要经过审查，确保符合 v6 最佳实践。

### 5. 性能监控
迁移前后对比性能指标，确保没有性能退化。

### 6. 增量发布
如果是生产项目，建议采用灰度发布策略。

---

## 🎉 完成检查清单

当你完成迁移后，检查以下项目：

- [ ] 所有 TypeScript 编译错误已修复
- [ ] 所有页面可以正常访问
- [ ] 核心功能测试通过
- [ ] 主题切换正常工作
- [ ] 国际化正常工作
- [ ] 移动端响应式正常
- [ ] 性能没有明显下降
- [ ] 控制台无报错和警告
- [ ] 代码已提交到 Git
- [ ] 团队成员已了解新特性

---

**祝你迁移顺利！🚀**

如有问题，请参考 `migration-checklist.md` 或查阅官方文档。
