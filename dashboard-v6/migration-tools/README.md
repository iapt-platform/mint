# 🚀 Ant Design v4 → v6 迁移工具包

完整的 Ant Design v4.24 到 v6 升级迁移工具包，包含自动化脚本和详细指南。

## 📦 工具包内容

| 文件 | 类型 | 说明 |
|------|------|------|
| **migration-checklist.md** | 📋 文档 | 详细的迁移检查清单（主文档） |
| **static-methods-migration.md** | 🚨 文档 | 静态方法迁移专项指南（重要！） |
| **scan-api-diff.js** | 🔍 脚本 | API 差异扫描器 |
| **import-replace.js** | 🔧 脚本 | 批量替换工具 |
| **replace-static-import.js** | 🔧 脚本 | 静态方法 import 替换工具 |
| **generate-global-utils.js** | 📝 脚本 | 全局单例工具生成器 |
| **component-template.js** | 📝 脚本 | 组件模板生成器 |
| **USAGE.md** | 📖 文档 | 详细使用指南 |
| **README.md** | 📄 文档 | 本文件 |

---

## 🚨 重要提醒：静态方法迁移

**你的项目大量使用了 `message.*`、`notification.*`、`Modal.*` 静态方法！**

这是 v4 → v6 迁移中**最重要**的变更之一。在 v6 中，这些方法必须在特定上下文中使用，否则会失效。

### 快速解决方案

我为你准备了**全局单例**迁移方案，只需 3 步：

```bash
# 1. 生成全局单例工具文件
node generate-global-utils.js ./src/utils

# 2. 批量替换 import 语句（预览）
node replace-static-import.js ./src --dry-run

# 3. 执行替换（备份）
node replace-static-import.js ./src --backup
```

✅ **优势**：最小改动，业务代码几乎不需要修改
📖 **详细说明**：查看 [static-methods-migration.md](./static-methods-migration.md)

---

## ⚡ 快速开始

### 前置要求

- Node.js 14+
- 已创建 Ant Design v6 新项目
- 保留 v4 旧项目代码（不要删除）

### 1️⃣ 扫描旧项目，了解需要修改的内容

```bash
node scan-api-diff.js /path/to/old-project/src
```

### 2️⃣ 生成主题系统文件（重要！）

```bash
node component-template.js --theme ./src/theme
```

### 3️⃣ 开始迁移组件

```bash
# 选择一个简单模块开始（推荐 general）
cp -r /path/to/old-project/src/components/general ./src/components/

# 批量替换简单的 API
node import-replace.js ./src/components/general --backup
```

### 4️⃣ 测试并重复

逐个模块迁移并测试，确保每个模块正常工作。

---

## 📚 核心功能

### 🔍 API 差异扫描器

扫描代码中使用的 v4 API，标记所有需要修改的地方。

```bash
# 扫描并生成报告
node scan-api-diff.js /path/to/old-project/src

# 导出 JSON 格式报告
node scan-api-diff.js /path/to/old-project/src --json=report.json
```

**报告示例：**
```
📊 Ant Design v4 → v6 API 差异扫描报告
===============================================================================
📁 扫描统计:
   总文件数: 581
   有问题的文件: 127

🚨 问题统计:
   🔴 Critical: 5 个    (必须立即修改)
   🟠 High: 43 个       (高优先级)
   🟡 Medium: 89 个     (中优先级)
   🟢 Low: 12 个        (低优先级)
```

### 🔧 批量替换工具

自动替换简单的 API 变更（visible → open, moment → dayjs 等）。

```bash
# 预览修改（强烈推荐先预览）
node import-replace.js ./src --dry-run

# 执行替换并备份
node import-replace.js ./src --backup
```

**自动处理的变更：**
- ✅ `visible` → `open`
- ✅ `onVisibleChange` → `onOpenChange`
- ✅ `moment` → `dayjs`
- ✅ `overlay` → `menu` (Dropdown)
- ✅ `BackTop` → `FloatButton.BackTop`

### 📝 组件模板生成器

生成符合 v6 最佳实践的组件模板。

```bash
# 生成基础组件
node component-template.js basic UserCard ./src/components

# 生成表单组件
node component-template.js form UserForm ./src/components

# 生成 ProTable 组件
node component-template.js proTable UserList ./src/components

# 生成完整主题系统（重要！）
node component-template.js --theme ./src/theme
```

**可用模板：**
- `basic` - 基础组件
- `form` - 表单组件（含验证）
- `modal` - Modal 弹窗组件
- `table` - Table 列表组件
- `proTable` - ProTable 高级表格

---

## 🎯 推荐工作流程（针对你的项目）

### ⚡ 推荐方案：先处理静态方法

因为你的项目大量使用静态方法，建议优先处理：

```bash
# === Phase 0: 处理静态方法（最重要！）===

# 1. 生成全局单例工具
node generate-global-utils.js ./src/utils

# 2. 批量替换 import（预览）
node replace-static-import.js ./src --dry-run

# 3. 确认无误后执行替换
node replace-static-import.js ./src --backup

# 4. 在 src/index.tsx 中使用 AppProvider
# 参考 static-methods-migration.md


# === Phase 1: 评估和准备 ===

# 5. 扫描旧项目代码
node scan-api-diff.js /path/to/old-project/src --json=report.json

# 6. 生成主题系统
node component-template.js --theme ./src/theme


# === Phase 2: 逐步迁移 ===

# 7. 复制 general 组件
cp -r /path/to/old-project/src/components/general ./src/components/

# 8. 批量替换简单 API
node import-replace.js ./src/components/general --backup

# 9. 测试该模块功能

# 10. 重复步骤 7-9，迁移其他模块


# === Phase 3: 最终检查 ===

# 11. 扫描新项目，确认无遗漏
node scan-api-diff.js ./src
```

### 方案对比

| 步骤 | 不处理静态方法 | 处理静态方法（推荐） |
|------|---------------|---------------------|
| 工作量 | 大（每个文件都要改） | 小（批量替换） |
| 风险 | 高（容易遗漏） | 低（自动化） |
| 耗时 | 20-30 小时 | 5-7 小时 |
| 是否推荐 | ❌ | ✅ |

---

```bash
# 1. 评估工作量
node scan-api-diff.js /path/to/old-project/src --json=report.json

# 2. 设置主题系统
node component-template.js --theme ./src/theme

# 3. 逐个模块迁移
#    a. 复制模块代码
cp -r /path/to/old-project/src/components/general ./src/components/

#    b. 批量替换
node import-replace.js ./src/components/general --backup

#    c. 测试该模块

# 4. 重复步骤 3，直到所有模块迁移完成

# 5. 最终检查
node scan-api-diff.js ./src
```

### 方案二：快速型（小型项目推荐）

```bash
# 1. 复制所有代码
cp -r /path/to/old-project/src/* ./src/

# 2. 批量替换
node import-replace.js ./src --backup

# 3. 设置主题
node component-template.js --theme ./src/theme

# 4. 检查剩余问题
node scan-api-diff.js ./src

# 5. 手动修复剩余问题
```

---

## 📋 详细文档

- **[migration-checklist.md](./migration-checklist.md)** - 📋 完整的分阶段迁移清单
  - Phase 1: 基础设施层
  - Phase 2: 通用组件层 ⭐
  - Phase 3: 业务组件层
  - Phase 4: 样式和主题系统
  - Phase 5: 测试和优化

- **[USAGE.md](./USAGE.md)** - 📖 详细使用指南
  - 每个工具的详细说明
  - 实战示例
  - 常见问题 FAQ
  - 最佳实践建议

---

## ⚠️ 重要提醒

### 关于项目特性

你的项目大量使用以下组件，需要特别注意：

- ✅ **ProTable** - 基本兼容，但部分 API 有调整
- ✅ **ProList** - 基本兼容
- ✅ **ProForm** - 基本兼容，建议查看最新文档
- ✅ **Button** - 完全兼容
- ✅ **Tag** - 基本兼容
- ✅ **Card** - 基本兼容，新增 `classNames` API
- ✅ **Popover** - `visible` → `open`

### 关于主题系统

你的项目原本使用 CSS 方式管理主题（`theme/antd.dark.css`）：

- ❌ **v6 已废弃 CSS 方式**
- ✅ **改用 ConfigProvider + Design Token**
- ✅ 使用 `component-template.js --theme` 生成新主题系统
- ✅ 新系统支持动态切换，无需刷新页面

### 迁移优先级

建议按以下顺序迁移：

1. **Phase 1**: 基础设施（类型、Redux、路由）
2. **Phase 2**: 通用组件（`components/general/*`）⭐ 优先
3. **Phase 3**: 核心业务模块（`article`, `channel`, `corpus`）
4. **Phase 4**: 其他业务模块
5. **Phase 5**: 样式优化和测试

---

## 🔥 常见问题

### Q: 替换工具安全吗？

A: 请务必：
- ✅ 先使用 `--dry-run` 预览
- ✅ 使用 `--backup` 备份
- ✅ 使用 Git 版本控制

### Q: 扫描器报告太多问题怎么办？

A: 分优先级处理：
1. 先修复 🔴 Critical 和 🟠 High
2. 逐步处理 🟡 Medium
3. 最后优化 🟢 Low

### Q: 某些组件无法自动迁移怎么办？

A: 
- 参考 `migration-checklist.md` 中的 API 对照表
- 使用 `component-template.js` 生成参考模板
- 查阅 Ant Design 官方文档

### Q: 主题系统如何集成？

A:
```bash
# 1. 生成主题文件
node component-template.js --theme ./src/theme

# 2. 在 src/index.tsx 中引入
import AppProvider from './theme/AppProvider';

ReactDOM.render(
  <AppProvider>
    <App />
  </AppProvider>,
  document.getElementById('root')
);
```

---

## 📊 项目统计

基于你的项目结构分析：

```
总文件数: 581
├── 组件数: 500+
├── API 文件: 21
├── Redux reducers: 29
├── 页面: 8
└── 国际化: 3 种语言 (en-US, zh-Hans, zh-Hant)
```

**预估工作量：**
- 🔴 Critical 问题: 预计 1-2 天
- 🟠 High 问题: 预计 3-5 天
- 🟡 Medium 问题: 预计 5-7 天
- 🟢 Low 问题: 预计 1-2 天
- **总计: 约 10-16 天**（取决于项目复杂度和团队规模）

---

## 🛠️ 技术支持

### 遇到问题？

1. 查看 [USAGE.md](./USAGE.md) 详细文档
2. 检查 [migration-checklist.md](./migration-checklist.md) 相关章节
3. 参考 [Ant Design 官方文档](https://ant.design)

### 贡献改进

如果你发现工具的 bug 或有改进建议：
- 修改相应的脚本文件
- 更新文档
- 分享给团队

---

## 📝 版本历史

- **v1.0.0** (2026-02-14)
  - 初始版本
  - 包含 3 个核心工具
  - 完整的迁移文档

---

## 📄 许可证

这些工具脚本为你的项目专用，可自由修改和使用。

---

## 🎉 开始迁移

现在你已经准备好了！

1. 阅读 `migration-checklist.md` 了解整体流程
2. 运行 `scan-api-diff.js` 评估工作量
3. 使用 `component-template.js --theme` 设置主题
4. 开始逐个模块迁移

**祝你迁移顺利！🚀**

---

## 💡 提示

- 💾 记得经常提交代码到 Git
- 📝 在 `migration-checklist.md` 中记录进度
- 🧪 每迁移一个模块就测试一次
- 👥 必要时寻求团队协作

---

*最后更新: 2026-02-14*
