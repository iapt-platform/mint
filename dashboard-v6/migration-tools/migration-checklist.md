# Ant Design v4.24 → v6 迁移检查清单

> 项目迁移开始日期: 2026-02-14
> 策略: 在新脚手架上重构，保留 v4 代码不变

---

## 📊 迁移进度总览

- [ ] **Phase 1: 基础设施层** (0/15)
- [ ] **Phase 2: 通用组件层** (0/12)
- [ ] **Phase 3: 业务组件层** (0/45+)
- [ ] **Phase 4: 样式和主题** (0/5)
- [ ] **Phase 5: 测试和优化** (0/8)

**总体进度: 0% (0/85)**

---

## 🎯 Phase 1: 基础设施层

### 1.1 类型定义和工具

- [x] `src/types/article.ts` - 文章类型定义
- [x] `src/types/chat.ts` - 聊天类型定义
- [x] `src/types/search.ts` - 搜索类型定义
- [x] `src/utils.ts` - 工具函数迁移
- [x] `src/request.ts` - HTTP 请求封装
- [x] `src/hooks.ts` - 自定义 hooks

**注意事项:**

- ✅ 类型定义通常不受 antd 版本影响，可直接复制
- ⚠️ 检查是否有引用 antd 组件类型的地方

### 1.2 Redux 状态管理

- [x] `src/store.ts` - Store 配置
- [x] `src/reducers/` - 所有 29 个 reducer 文件
  - [ ] accept-pr.ts
  - [ ] article-mode.ts
  - [ ] cart-mode.ts
  - [ ] command.ts
  - [ ] course-user.ts
  - [ ] current-course.ts
  - [ ] current-user.ts
  - [ ] discussion-count.ts
  - [ ] discussion.ts
  - [ ] focus.ts
  - [ ] inline-dict.ts
  - [ ] layout.ts
  - [ ] net-status.ts
  - [ ] nissaya-ending-vocabulary.ts
  - [ ] open-article.ts
  - [ ] para-change.ts
  - [ ] pr-load.ts
  - [ ] relation-add.ts
  - [ ] relation.ts
  - [ ] right-panel.ts
  - [ ] sentence.ts
  - [ ] sent-word.ts
  - [ ] setting.ts
  - [ ] suggestion.ts
  - [ ] term-change.ts
  - [ ] term-click.ts
  - [ ] term-order.ts
  - [ ] term-vocabulary.ts
  - [ ] theme.ts
  - [ ] wbw.ts

**注意事项:**

- ✅ Redux 逻辑与 antd 版本无关，可直接迁移
- ⚠️ 检查 `theme.ts` reducer，需要适配新的主题系统

### 1.3 路由和布局

- [ ] `src/Router.tsx` - 路由配置
- [ ] `src/layouts/anonymous/index.tsx` - 匿名布局
- [ ] `src/layouts/dashboard/index.tsx` - 仪表盘布局

**注意事项:**

- ⚠️ Layout 组件在 v6 中有 API 变更
- ⚠️ 响应式断点值有调整

---

## 🎯 Phase 2: 通用组件层 ⭐ **当前优先级**

### 2.1 General 组件 (src/components/general/\*)

- [ ] `BeiAn.tsx` - 备案信息
- [ ] `EditableLabel.tsx` - 可编辑标签
- [ ] `ErrorResult.tsx` - 错误结果页
- [ ] `Feedback.tsx` - 反馈组件
- [ ] `FileSize.tsx` - 文件大小显示
- [ ] `LangSelect.tsx` - 语言选择器
- [ ] `Marked.tsx` - Markdown 渲染
- [ ] `Mermaid.tsx` - Mermaid 图表
- [ ] `NetStatus.tsx` - 网络状态
- [ ] `NissayaCard.tsx` & `NissayaCardTable.tsx` - 卡片组件
- [ ] `ParserError.tsx` - 解析错误
- [ ] `ReadonlyLabel.tsx` - 只读标签
- [ ] `SearchButton.tsx` - 搜索按钮
- [ ] `TermTextArea.tsx` & `TermTextAreaMenu.tsx` - 文本区域
- [ ] `TextDiff.tsx` - 文本差异对比
- [ ] `ThemeSelect.tsx` - 主题选择器 ⚠️ **需重写**
- [ ] `TimeShow.tsx` - 时间显示
- [ ] `UiLangSelect.tsx` - UI 语言选择
- [ ] `VideoModal.tsx` & `VideoPlayer.tsx` & `Video.tsx` - 视频组件
- [ ] `VisibleObserver.tsx` - 可见性观察器

**迁移优先级:**

1. 🔴 高优先级: `ThemeSelect.tsx` (需要重写)
2. 🟡 中优先级: 表单相关、选择器相关
3. 🟢 低优先级: 纯展示组件

### 2.2 Auth 认证组件 (src/components/auth/\*)

- [ ] `Account.tsx` - 账户信息
- [ ] `Avatar.tsx` - 头像组件
- [ ] `LoginAlertModal.tsx` & `LoginAlert.tsx` - 登录提示
- [ ] `LoginButton.tsx` - 登录按钮
- [ ] `SignInAvatar.tsx` - 登录头像
- [ ] `SoftwareEdition.tsx` - 软件版本
- [ ] `StudioCard.tsx` & `Studio.tsx` - 工作室
- [ ] `ToLibrary.tsx` & `ToStudio.tsx` - 导航组件
- [ ] `User.tsx` - 用户组件

### 2.3 API 层 (src/components/api/\*)

- [ ] 复制所有 21 个 API 文件（通常不受影响）
- [ ] 验证 API 调用是否正常

---

## 🎯 Phase 3: 业务组件层

### 3.1 核心组件 - ProComponents ⚠️ **重点关注**

你的项目大量使用 ProComponents，需要特别注意：

#### ProTable 相关

- [ ] `src/components/pro-table/*` - 自定义 ProTable 组件
- [ ] 所有使用 ProTable 的业务组件

**v4 → v6 ProTable 主要变更:**

```typescript
// v4
<ProTable
  rowKey="id"
  request={async (params) => { ... }}
  columns={columns}
/>

// v6 (主要兼容，但有细节调整)
<ProTable
  rowKey="id"
  request={async (params, sort, filter) => { ... }} // 参数顺序可能变化
  columns={columns}
  // 新增: cardProps, polling 等属性
/>
```

#### ProForm 相关

**主要变更:**

- Form.Item 不再需要 `name` 和 `label` 同时存在
- `dependencies` 写法优化
- 表单联动更简洁

#### ProList 相关

**主要变更:**

- `metas` 配置优化
- 响应式布局改进

### 3.2 Article 文章模块 (56个文件)

- [ ] `ArticleCard.tsx` & 相关卡片组件
- [ ] `ArticleEdit.tsx` & 编辑相关组件
- [ ] `ArticleList.tsx` & 列表相关组件
- [ ] `TocTree.tsx` & 目录相关组件
- [ ] ... (其他 50+ 文件)

### 3.3 Channel 频道模块

- [ ] 迁移所有 channel 组件

### 3.4 Corpus 语料模块

- [ ] 迁移所有 corpus 组件

### 3.5 Course 课程模块

- [ ] 迁移所有 course 组件

### 3.6 其他业务模块

- [ ] Dict 词典模块
- [ ] Term 术语模块
- [ ] Task 任务模块
- [ ] Discussion 讨论模块
- [ ] AI 相关模块
- [ ] Anthology 文集模块
- [ ] Attachment 附件模块
- [ ] Blog 博客模块
- [ ] Chat 聊天模块
- [ ] Export 导出模块
- [ ] FTS 全文搜索模块
- [ ] Group 组模块
- [ ] Invite 邀请模块
- [ ] Like 点赞模块
- [ ] Notification 通知模块
- [ ] Recent 最近访问模块
- [ ] Share 分享模块
- [ ] Tag 标签模块
- [ ] Transfer 转移模块
- [ ] Webhook 模块

---

## 🎯 Phase 4: 样式和主题系统 ⚠️ **重大变更**

### 4.1 主题迁移 - 从 CSS 到 ConfigProvider

**❌ v4 方式 (废弃):**

```css
/* theme/antd.dark.css */
.ant-btn-primary {
  background-color: #1890ff;
}
```

**✅ v6 方式 (使用 Design Token):**

```typescript
import { ConfigProvider, theme } from 'antd';

<ConfigProvider
  theme={{
    algorithm: theme.darkAlgorithm, // 暗黑主题
    token: {
      colorPrimary: '#00b96b',
      borderRadius: 2,
      // ... 其他 token
    },
    components: {
      Button: {
        colorPrimary: '#00b96b',
      },
      // ... 其他组件定制
    }
  }}
>
  <App />
</ConfigProvider>
```

### 4.2 主题切换任务清单

- [ ] **删除旧文件**: `src/theme/antd.dark.css`
- [ ] **删除旧文件**: `src/theme/antpro.dark.css`
- [ ] **创建新组件**: `src/components/theme/ThemeProvider.tsx` (主题提供者)
- [ ] **重写组件**: `src/components/general/ThemeSelect.tsx` (主题切换器)
- [ ] **创建配置**: `src/theme/tokens.ts` (Design Token 配置)

**新主题系统特性:**

- ✅ 动态主题切换（无需刷新页面）
- ✅ CSS 变量支持
- ✅ 组件级主题定制
- ✅ 暗黑模式内置支持
- ✅ 更好的 TypeScript 类型提示

### 4.3 自定义样式迁移

- [ ] `src/assets/font/main.css` - 字体样式
- [ ] `src/components/article/article.css` - 文章样式
- [ ] `src/components/dict/style.css` - 词典样式
- [ ] `src/components/fts/search.css` - 搜索样式
- [ ] `src/components/general/style.css` - 通用样式
- [ ] `src/components/chat/style.css` - 聊天样式
- [ ] `src/components/template/style.css` - 模板样式

**检查要点:**

- ⚠️ 是否有覆盖 antd 默认样式的 CSS 选择器
- ⚠️ 类名是否有变化 (如 `.ant-btn` → 可能保持不变)
- ⚠️ 使用 less 变量的地方需要改为 CSS 变量或 Design Token

---

## 🎯 Phase 5: 测试和优化

### 5.1 功能测试

- [ ] 登录/注册流程
- [ ] 文章 CRUD 操作
- [ ] 课程管理
- [ ] 搜索功能
- [ ] 主题切换
- [ ] 国际化切换
- [ ] 移动端响应式

### 5.2 性能优化

- [ ] 检查包体积 (对比 v4 vs v6)
- [ ] 组件懒加载
- [ ] Tree-shaking 验证

### 5.3 兼容性测试

- [ ] Chrome 最新版
- [ ] Firefox 最新版
- [ ] Safari 最新版
- [ ] Edge 最新版
- [ ] 移动端浏览器

---

## 📚 关键 API 变更速查表

### 常用组件 API 对照

#### Button

```typescript
// ✅ 基本兼容，无重大变更
<Button type="primary">Click</Button>
```

#### Tag

```typescript
// ✅ 基本兼容
// ⚠️ 注意: closeIcon 属性有调整
<Tag closable onClose={handleClose}>Tag</Tag>
```

#### Card

```typescript
// ✅ 基本兼容
// 新增: classNames, styles 属性用于更细粒度控制
<Card
  title="Title"
  extra={<Button>More</Button>}
  classNames={{ header: 'custom-header' }} // v6 新增
>
  Content
</Card>
```

#### Popover

```typescript
// ⚠️ 有变更
// v4
<Popover
  visible={visible}  // ❌ 废弃
  onVisibleChange={handleChange}  // ❌ 废弃
>

// v6
<Popover
  open={open}  // ✅ 新属性
  onOpenChange={handleChange}  // ✅ 新属性
>
```

#### Modal

```typescript
// ⚠️ 重要变更
// v4
Modal.confirm({
  title: 'Confirm',
  visible: true,  // ❌ 废弃
})

// v6
const [modal, contextHolder] = Modal.useModal(); // ✅ 推荐用 hook
modal.confirm({
  title: 'Confirm',
  open: true,  // ✅ 新属性
})

// 或使用静态方法（需要包裹 App 组件）
<App>
  {contextHolder}
  <YourComponent />
</App>
```

#### Message / Notification

```typescript
// ⚠️ 重要变更 - 必须使用 hook 方式

// v4 (不推荐)
import { message } from 'antd';
message.success('Success');

// v6 (推荐)
import { message, App } from 'antd';

const MyComponent = () => {
  const { message } = App.useApp();

  const showMessage = () => {
    message.success('Success');
  };

  return <Button onClick={showMessage}>Show</Button>;
};

// 在根组件包裹
<App>
  <MyComponent />
</App>
```

#### Form (ProForm 也适用)

```typescript
// ⚠️ 部分 API 调整

// v4
<Form.Item
  name="username"
  rules={[{ required: true, message: 'Required' }]}
>
  <Input />
</Form.Item>

// v6 (基本兼容，但推荐使用新 API)
<Form.Item
  name="username"
  rules={[{ required: true, message: 'Required' }]}
>
  <Input />
</Form.Item>

// v6 新特性: Form.useWatch
const username = Form.useWatch('username', form);
```

#### Table / ProTable

```typescript
// ⚠️ 分页、筛选 API 有调整

// v4
<ProTable
  pagination={{
    defaultCurrent: 1,
    defaultPageSize: 10,
  }}
  onChange={(pagination, filters, sorter) => {}}
/>

// v6 (基本兼容，但有新特性)
<ProTable
  pagination={{
    defaultCurrent: 1,
    defaultPageSize: 10,
  }}
  onChange={(pagination, filters, sorter, extra) => {
    // extra.action: 'paginate' | 'sort' | 'filter'
  }}
/>
```

#### DatePicker

```typescript
// ⚠️ 重大变更: moment.js → dayjs

// v4
import moment from 'moment';
<DatePicker defaultValue={moment()} />

// v6
import dayjs from 'dayjs';
<DatePicker defaultValue={dayjs()} />

// 需要全局替换所有 moment 引用为 dayjs
```

---

## 🔧 常见问题和解决方案

### 1. TypeScript 类型错误

```typescript
// 问题: Property 'visible' does not exist
// 解决: 替换为 'open'
<Modal visible={true} />  // ❌
<Modal open={true} />     // ✅
```

### 2. 样式突然失效

```typescript
// 问题: 自定义 CSS 不生效
// 解决: 检查类名是否变化，或使用 ConfigProvider
.ant-btn-custom { ... }  // 可能失效
// 改用 Design Token 或 classNames API
```

### 3. 静态方法调用报错

```typescript
// 问题: message.success() 不显示
// 解决: 必须包裹 <App> 组件
<App>
  <YourComponent />
</App>
```

### 4. less 变量失效

```typescript
// 问题: @primary-color 不起作用
// 解决: 迁移到 ConfigProvider token
// v4
@primary-color: #1890ff;

// v6
<ConfigProvider theme={{ token: { colorPrimary: '#1890ff' } }}>
```

---

## 📝 迁移日志模板

在每次迁移组件后，建议记录日志：

```markdown
### 2026-02-14 - 迁移 ThemeSelect 组件

**迁移内容:**

- [x] 从 v4 CSS 方式改为 v6 ConfigProvider
- [x] 新增 Design Token 配置
- [x] 支持亮色/暗黑主题切换

**遇到的问题:**

1. 问题描述...
   解决方案: ...

**测试结果:**

- [x] 功能正常
- [x] 样式正确
- [ ] 待补充单元测试

**备注:**
暗黑模式性能优于 v4，切换无闪烁
```

---

## 🎯 下一步行动

### 立即开始

1. ✅ 已创建本 checklist
2. ⏭️ 运行辅助脚本扫描代码
3. ⏭️ 迁移 `components/general/*` 第一批组件
4. ⏭️ 重写 `ThemeSelect.tsx` 和主题系统

### 需要帮助时

- 🔍 搜索本文档关键词
- 📖 参考 [Ant Design v6 官方文档](https://ant.design/docs/react/migration-v5)
- 💬 询问 AI 助手具体组件迁移方法

---

## 📌 重要提醒

1. **不要一次性迁移所有组件** - 逐个模块验证
2. **每迁移一个组件就测试** - 避免问题累积
3. **保持 v4 代码可运行** - 方便对比和回退
4. **记录所有自定义修改** - 便于后续维护
5. **主题系统优先迁移** - 它影响所有组件样式

---

**祝迁移顺利！🚀**

有任何问题随时在本文件顶部的"迁移日志"区域记录，或咨询 AI 助手。
