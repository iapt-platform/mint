# 📋 项目迁移 Todo List

## 🧭 二、路由结构迁移

### 2️⃣ Basic 模块

- [x] `/palicanon`=>`workgroup/tipitaka`
- [x] `/recent/list`=>`workgroup/recent`
- [x] `/channel/list`=>`workgroup/channel`
- [ ] `/exp/list` [3]
- [x] `/setting` [3]
- [x] `/ai/models/list`=>`resources/ai-models` [3]

---

### 3️⃣ Advance 模块

#### Task 子模块 [2]

- [x] `/task/hall`=>`workgroup/task`
- [x] `/task/list`=>`workgroup/task`
- [x] `/task/projects`=>`workgroup/task`
- [x] `/task/workflows`=>`workgroup/task`

#### 内容模块

- [x] `/course/list`=>`workgroup/course` [2]
- [x] `/dict/list`=>`resources/dict` [1]
- [x] `/term/list`=>`workgroup/term`
- [x] `/article/list`=>`workgroup/article`
- [x] `/anthology/list`=>`workgroup/anthology`
- [x] `/attachment/list`=>`resources/attachment`
- [x] `/tags/list`=>`resources/tags` [3]

---

### 4️⃣ Collaboration 模块

- [x] `/group/list`=>`collaboration/team` [3]
- [x] `/invite/list`=>`collaboration/invite` [3]
- [x] `/transfer/list`=>`collaboration/transfer` [3]

---

### 新增

- [ ] `/discussion`=> [1]

### article page

- [x] "anthology"
- [x] "article"
- [x] "textbook"
- [x] "term"
- [x] "chapter"
- [x] "para"
- [ ] "series"
- [ ] "cs-para"

### 5️⃣ Admin 模块（`/admin`，仅 root / administrator）

- [x] `/admin/api/dashboard`=>`admin/api`
- [x] `/admin/relation/list`=>`admin/relation`
- [x] `/admin/nissaya-ending/list`（含 `/list/:relation`）=>`admin/nissaya-ending`（`/:relation`）
- [x] `/admin/dictionary/list`=>`admin/dictionary`
- [x] `/admin/dictionary/preference`=>`admin/dictionary/preference`
- [x] `/admin/users/list`、`/admin/users/show/:id`=>`admin/users`、`admin/users/:id`
- [x] `/admin/invite/list`=>`admin/invite`
- [x] `/admin/ai/list`=>`admin/ai-models`
- [x] 新增：`admin/stats/reading` 阅读统计、`admin/stats/ai` AI 对话统计
