# 聊天系统数据库设计文档

## 1. 项目概述

本项目旨在实现一个类似 Claude 的聊天系统，支持以下核心功能：

- 多轮对话管理
- Function Call 集成
- 消息树结构（支持消息分支和版本控制）
- 用户可修改问题并生成新的回答分支
- 支持切换不同版本的回答
- 支持更换模型重新回答同一问题
- 消息生成参数跟踪（temperature、tokens 等）

## 2. 核心设计理念

### 2.1 消息组织结构

- **Chat**: 每次点击"新建聊天"创建一个独立的消息树
- **Session**: 将相关的消息组织成会话段，前端显示为一个消息组
- **Message Tree**: 通过 parent_id 维护消息间的依赖关系
- **Version Control**: 支持同一节点的多个版本，用户可切换查看

### 2.2 Function Call 集成

系统支持标准的 Function Call 流程：

```text
user → assistant(tool_calls) → tool(result) → assistant(final_response)
```

所有相关消息归属同一个 session，前端显示为一个完整的 AI 回复。

### 2.3 软删除机制

系统采用软删除策略，保护用户数据安全：

- 所有删除操作仅标记 `deleted_at` 字段
- 查询时默认过滤已删除数据
- 支持数据恢复和审计需求

## 3. 数据库表设计

### 3.1 chats 表（聊天会话）

存储每个独立的聊天会话。

```sql
CREATE TABLE chats (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    uid CHAR(36) NOT NULL UNIQUE COMMENT 'UUID唯一标识',
    title VARCHAR(255) NOT NULL COMMENT '聊天标题',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT '软删除时间戳',
    user_id CHAR(36) COMMENT '用户ID',

    INDEX idx_chats_uid (uid),
    INDEX idx_chats_user_id (user_id),
    INDEX idx_chats_created_at (created_at),
    INDEX idx_chats_deleted_at (deleted_at)
) COMMENT '聊天会话表';
```

### 3.2 chat_messages 表（消息节点）

存储所有消息，支持树形结构和版本控制。role=system 为消息树的唯一根节点。不可修改，不可删除。

```sql
CREATE TABLE chat_messages (
    id BIGINT AUTO_INCREMENT PRIMARY KEY COMMENT '自增主键，提供天然时间排序',
    uid CHAR(36) NOT NULL UNIQUE COMMENT 'UUID唯一标识',
    chat_id CHAR(36) NOT NULL COMMENT '关联chats.uid',
    parent_id CHAR(36) COMMENT '关联chat_messages.uid，NULL表示根节点',
    session_id CHAR(36) NOT NULL COMMENT '会话段ID，同一消息组共享',

    role ENUM('system','user', 'assistant', 'tool') NOT NULL COMMENT '消息角色',
    content TEXT COMMENT '消息内容',
    model_id CHAR(36) COMMENT '使用的模型id（assistant消息）',
    tool_calls JSON COMMENT '函数调用信息（assistant消息）',
    tool_call_id VARCHAR(100) COMMENT '工具调用ID（tool消息）',
    metadata JSON COMMENT '消息元数据：生成参数、token统计等',

    is_active BOOLEAN DEFAULT TRUE COMMENT '是否为当前激活版本',
    editor_id CHAR(36) COMMENT '最后编辑用户ID',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT '软删除时间戳',

    INDEX idx_messages_uid (uid),
    INDEX idx_messages_chat_id (chat_id),
    INDEX idx_messages_parent_id (parent_id),
    INDEX idx_messages_session_id (session_id),
    INDEX idx_messages_active (chat_id, is_active),
    INDEX idx_messages_deleted_at (deleted_at)
) COMMENT '聊天消息表';
```

## 4. 数据结构说明

### 4.1 字段详解

#### chats 表字段

- `id`: 自增主键，数据库内部使用
- `uid`: UUID 标识，用于外部引用和 API
- `title`: 聊天标题，可从首条消息自动生成
- `user_id`: 用户标识（可选，如果需要多用户支持）
- `deleted_at`: 软删除时间戳，NULL 表示未删除

#### chat_messages 表字段

- `id`: 自增主键，提供天然的消息时间顺序
- `uid`: UUID 标识，用于 parent_id 引用，保证跨系统稳定性
- `parent_id`: 指向父消息的 uid，构建技术依赖关系
- `session_id`: 消息组标识，相同 session_id 的消息在前端显示为一组
- `role`: 消息类型（user 用户/assistant 助手/tool 工具结果）
- `tool_calls`: JSON 格式存储函数调用信息
- `tool_call_id`: 关联 tool 消息到具体的函数调用
- `metadata`: 消息生成相关参数和统计信息
- `is_active`: 标记用户当前查看的版本
- `deleted_at`: 软删除时间戳，NULL 表示未删除

### 4.2 metadata 字段结构

metadata 字段存储与消息生成相关的参数和统计信息：

```json
{
  // AI生成参数（assistant消息）
  "generation_params": {
    "temperature": 0.7,
    "max_tokens": 2048,
    "top_p": 0.9,
    "frequency_penalty": 0.0,
    "presence_penalty": 0.0
  },

  // Token使用统计
  "token_usage": {
    "prompt_tokens": 150,
    "completion_tokens": 320,
    "total_tokens": 470
  },

  // 性能指标
  "performance": {
    "response_time_ms": 1250,
    "first_token_time_ms": 450
  },

  // 工具调用统计（如适用）
  "tool_stats": {
    "total_calls": 2,
    "successful_calls": 2,
    "execution_time_ms": 340
  },

  // 其他扩展信息
  "custom_data": {}
}
```

### 4.3 消息关系说明

#### parent_id 关系（技术依赖）

维护 Function Call 的执行链：

```text
user(id=1)
  ↓ parent_id=uuid1
assistant(id=2, tool_calls=[...])
  ↓ parent_id=uuid2
tool(id=3, tool_call_id=xxx)
  ↓ parent_id=uuid3
assistant(id=4, final_response)
```

#### session_id 关系（显示分组）

将相关消息组织成前端显示单元：

```text
Session 1: [user消息]
Session 2: [assistant + tool + assistant] -> 显示为一个AI回复
Session 3: [user消息]
```

## 5. 软删除实现

### 5.1 删除操作

```sql
-- 软删除聊天（级联删除所有消息）
UPDATE chats SET deleted_at = CURRENT_TIMESTAMP WHERE uid = ?;
UPDATE chat_messages SET deleted_at = CURRENT_TIMESTAMP WHERE chat_id = ?;

-- 软删除单个消息
UPDATE chat_messages SET deleted_at = CURRENT_TIMESTAMP WHERE uid = ?;
```

### 5.2 查询过滤

所有业务查询都需要过滤已删除数据：

```sql
-- 查询示例
SELECT * FROM chats WHERE deleted_at IS NULL;
SELECT * FROM chat_messages WHERE deleted_at IS NULL;
```

### 5.3 数据恢复

```sql
-- 恢复聊天
UPDATE chats SET deleted_at = NULL WHERE uid = ?;

-- 恢复消息
UPDATE chat_messages SET deleted_at = NULL WHERE uid = ?;
```

## 6. 典型数据示例

### 6.1 Function Call 对话示例

```sql
-- Chat记录
INSERT INTO chats (id, uid, title) VALUES
(1, 'chat-uuid-1', '天气查询对话');

-- 消息记录
INSERT INTO chat_messages (id, uid, chat_id, parent_id, session_id, role, content, metadata) VALUES
-- Session 1: 用户提问
(1, 'msg-uuid-1', 'chat-uuid-1', NULL, 'session-uuid-1', 'user', '查询今天天气', NULL),

-- Session 2: AI处理流程
(2, 'msg-uuid-2', 'chat-uuid-1', 'msg-uuid-1', 'session-uuid-2', 'assistant', NULL,
 '{"generation_params":{"temperature":0.7,"max_tokens":2048},"token_usage":{"prompt_tokens":20,"completion_tokens":50,"total_tokens":70}}'),

(3, 'msg-uuid-3', 'chat-uuid-1', 'msg-uuid-2', 'session-uuid-2', 'tool', '今天晴天，25°C',
 '{"tool_stats":{"execution_time_ms":340}}'),

(4, 'msg-uuid-4', 'chat-uuid-1', 'msg-uuid-3', 'session-uuid-2', 'assistant', '今天天气晴朗，气温25度...',
 '{"generation_params":{"temperature":0.7},"token_usage":{"prompt_tokens":100,"completion_tokens":80,"total_tokens":180},"performance":{"response_time_ms":1250}}'),

-- Session 3: 用户继续提问
(5, 'msg-uuid-5', 'chat-uuid-1', 'msg-uuid-4', 'session-uuid-3', 'user', '那明天呢？', NULL);
```

### 6.2 tool_calls JSON 结构示例

```json
[
  {
    "id": "call_123",
    "function": "get_weather",
    "arguments": {
      "city": "北京",
      "date": "today"
    }
  }
]
```

## 7. 核心业务场景

### 7.1 用户修改问题

1. 软删除原消息及其后续回答链
2. 创建新版本消息
3. 生成新的回答链，记录生成参数到 metadata

### 7.2 重新生成回答

1. 软删除当前 assistant 回答
2. 使用相同或不同的参数重新生成
3. 在 metadata 中记录新的生成参数和统计信息

### 7.3 消息分支管理

- 每个分支保持独立的 session_id
- 通过 parent_id 维护分支关系
- 软删除支持分支恢复

## 8. 常用查询模式

### 8.1 获取聊天的消息组列表

```sql
SELECT DISTINCT session_id, MIN(id) as first_message_id
FROM chat_messages
WHERE chat_id = ? AND is_active = true AND deleted_at IS NULL
ORDER BY first_message_id;
```

### 8.2 获取某个 session 的所有消息

```sql
SELECT * FROM chat_messages
WHERE session_id = ? AND is_active = true AND deleted_at IS NULL
ORDER BY id;
```

### 8.3 获取完整对话历史（用于 AI Context）

```sql
-- 获取当前激活路径的所有消息
WITH RECURSIVE active_path AS (
  SELECT * FROM chat_messages
  WHERE chat_id = ? AND parent_id IS NULL AND is_active = true AND deleted_at IS NULL

  UNION ALL

  SELECT m.* FROM chat_messages m
  JOIN active_path p ON m.parent_id = p.uid
  WHERE m.is_active = true AND m.deleted_at IS NULL
)
SELECT * FROM active_path ORDER BY id;
```

### 8.4 查询消息的所有版本（包含已删除）

```sql
SELECT *,
       CASE WHEN deleted_at IS NULL THEN 'active' ELSE 'deleted' END as status
FROM chat_messages
WHERE parent_id = ? AND role = ?
ORDER BY created_at;
```

### 8.5 Token 使用统计查询

```sql
-- 查询聊天的总token使用量
SELECT
    chat_id,
    SUM(JSON_EXTRACT(metadata, '$.token_usage.total_tokens')) as total_tokens,
    AVG(JSON_EXTRACT(metadata, '$.performance.response_time_ms')) as avg_response_time
FROM chat_messages
WHERE chat_id = ? AND role = 'assistant' AND deleted_at IS NULL
GROUP BY chat_id;
```

## 9. 性能优化建议

### 9.1 索引策略

- `chat_id`: 快速查询某个聊天的所有消息
- `session_id`: 快速获取消息组
- `parent_id`: 支持树形查询和兄弟节点查询
- `(chat_id, is_active)`: 快速获取激活消息
- `deleted_at`: 快速过滤软删除数据

### 9.2 查询优化

- 使用递归 CTE 进行树形查询
- 考虑 materialized path 优化深层树查询
- 对于频繁的 session 查询，考虑适当的缓存策略
- metadata JSON 字段可根据需要创建虚拟列索引

### 9.3 软删除优化

- 定期清理长期删除的数据（如 30 天后物理删除）
- 考虑分区表优化查询性能
- 为 deleted_at 字段建立部分索引（仅索引非 NULL 值）

## 10. 扩展性考虑

### 10.1 多模态支持

- content 字段可存储 JSON，支持文本、图片、文件等多种内容类型
- tool_calls 可扩展支持更多函数类型
- metadata 可扩展记录多模态内容的处理参数

### 10.2 权限控制

- 通过 user_id 支持多用户隔离
- 可扩展支持聊天分享、协作等功能
- 软删除支持权限恢复场景

### 10.3 审计和监控

- created_at/updated_at 支持操作时间追踪
- deleted_at 提供删除审计
- metadata 记录详细的生成和性能数据
- 可扩展添加操作日志表记录详细变更历史

## 11. 注意事项

1. **UUID 生成**: 确保 uid 字段使用标准 UUID v4 格式
2. **JSON 字段**: 使用数据库原生 JSON 类型，支持索引和查询
3. **外键约束**: 合理使用外键约束保证数据一致性
4. **软删除一致性**: 确保级联删除的数据一致性
5. **并发控制**: 在版本更新时注意并发冲突处理
6. **数据清理**: 制定合理的物理删除策略，避免数据无限增长
7. **查询习惯**: 所有业务查询都必须包含 `deleted_at IS NULL` 条件
8. **JSON 索引**: 根据实际查询需求为 metadata 字段创建合适的索引

---

**文档版本**: 1.1  
**创建时间**: 2025-09-07  
**更新时间**: 2025-09-09  
**修订说明**: 增加 metadata 字段和软删除机制
