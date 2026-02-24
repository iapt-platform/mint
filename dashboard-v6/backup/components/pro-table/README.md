# ProTable 组件

自定义实现的 ProTable 组件，用于替代 `@ant-design/pro-components` 的 ProTable。

## 功能特性

✅ 完整支持你当前代码中使用的所有功能：

### 核心功能

- **actionRef** - 表格操作引用，支持 `reload()` 和 `reset()` 方法
- **columns** - 列配置，支持所有你使用的属性
- **request** - 异步数据请求，自动处理分页、排序、过滤
- **rowKey** - 行唯一标识
- **pagination** - 分页配置

### 列配置 (ProColumns)

- `title` - 列标题
- `dataIndex` - 数据字段名
- `key` - 唯一标识
- `width` - 列宽
- `search` - 搜索配置
- `hideInTable` - 隐藏列
- `tooltip` - 提示信息
- `ellipsis` - 文本省略
- `valueType` - 值类型（date, dateTime, option 等）
- `valueEnum` - 枚举值（自动转换为过滤器）
- `render` - 自定义渲染函数
- `filters` - 过滤配置
- `onFilter` - 过滤函数
- `sorter` - 排序配置

### 工具栏

- `toolBarRender` - 自定义工具栏按钮
- `toolbar.menu` - 标签页切换（如 my/collaboration/community）
- `options.search` - 关键词搜索功能

## 安装使用

### 1. 复制文件

将 `ProTable.tsx` 复制到你的项目中。

### 2. 替换导入

```tsx
// 原来
import { ActionType, ProTable } from "@ant-design/pro-components";

// 现在
import ProTable, { ActionType } from "./components/ProTable";
```

### 3. 使用方式

```tsx
import { useRef } from "react";
import ProTable, { ActionType } from "./components/ProTable";

const MyComponent = () => {
  const ref = useRef<ActionType | null>(null);

  return (
    <ProTable
      actionRef={ref}
      columns={[
        {
          title: "序号",
          dataIndex: "id",
          key: "id",
          width: 50,
          search: false,
        },
        {
          title: "标题",
          dataIndex: "title",
          key: "title",
          ellipsis: true,
          render: (text, row, index, action) => {
            return <Button onClick={() => action.reload()}>{text}</Button>;
          },
        },
        {
          title: "类型",
          dataIndex: "type",
          key: "type",
          filters: true,
          onFilter: true,
          valueEnum: {
            all: { text: "全部" },
            translation: { text: "翻译" },
            original: { text: "原创" },
          },
        },
        {
          title: "创建时间",
          dataIndex: "created_at",
          key: "created_at",
          valueType: "date",
          sorter: true,
        },
      ]}
      request={async (params, sorter, filter) => {
        // 构建 API URL
        const url = `/api/data?page=${params.current}&size=${params.pageSize}`;

        // 发起请求
        const res = await fetch(url);
        const json = await res.json();

        return {
          data: json.rows,
          total: json.count,
          success: true,
        };
      }}
      rowKey="id"
      bordered
      pagination={{
        showQuickJumper: true,
        showSizeChanger: true,
      }}
      options={{
        search: true,
      }}
      toolBarRender={() => [
        <Button key="create" type="primary">
          新建
        </Button>,
      ]}
      toolbar={{
        menu: {
          activeKey: "my",
          items: [
            { key: "my", label: "我的" },
            { key: "all", label: "全部" },
          ],
          onChange: (key) => {
            console.log("切换到", key);
          },
        },
      }}
    />
  );
};
```

## API 说明

### ProTable Props

| 属性          | 说明         | 类型                                               | 默认值  |
| ------------- | ------------ | -------------------------------------------------- | ------- |
| columns       | 列配置       | `ProColumns[]`                                     | -       |
| request       | 数据请求函数 | `(params, sorter, filter) => Promise<RequestData>` | -       |
| actionRef     | 表格操作引用 | `React.MutableRefObject<ActionType>`               | -       |
| rowKey        | 行唯一标识   | `string \| (record) => string`                     | `'id'`  |
| bordered      | 是否显示边框 | `boolean`                                          | `false` |
| pagination    | 分页配置     | `false \| PaginationConfig`                        | -       |
| search        | 搜索配置     | `false \| SearchConfig`                            | `false` |
| options       | 选项配置     | `OptionsConfig`                                    | `{}`    |
| toolBarRender | 工具栏渲染   | `() => ReactNode[]`                                | -       |
| toolbar       | 工具栏配置   | `ToolbarConfig`                                    | -       |
| headerTitle   | 表格标题     | `ReactNode`                                        | -       |
| params        | 额外请求参数 | `Record<string, any>`                              | -       |

### ActionType

```typescript
interface ActionType {
  reload: (resetPageIndex?: boolean) => void; // 刷新表格
  reset: () => void; // 重置表格
}
```

### Request 函数参数

```typescript
async (params, sorter, filter) => {
  // params: { current: 1, pageSize: 20, keyword: '搜索词', ...自定义参数 }
  // sorter: { field_name: 'ascend' | 'descend' }
  // filter: { field_name: ['value1', 'value2'] }

  return {
    data: [], // 数据列表
    total: 0, // 总数
    success: true, // 是否成功
  };
};
```

## 与原 ProTable 的差异

### 保持一致的功能

✅ 所有你代码中使用的功能都已实现
✅ API 接口完全兼容
✅ TypeScript 类型支持

### 简化的部分

- 移除了未使用的高级功能（如拖拽排序、可编辑表格等）
- 简化了搜索表单（仅保留关键词搜索）
- 移除了列设置、密度调整等辅助功能

这些简化不影响你当前代码的运行。

## 迁移检查清单

- [ ] 复制 `ProTable.tsx` 到项目
- [ ] 更新导入语句
- [ ] 验证表格渲染正常
- [ ] 测试分页功能
- [ ] 测试搜索功能
- [ ] 测试过滤功能
- [ ] 测试排序功能
- [ ] 测试 actionRef.reload()
- [ ] 测试工具栏切换

## 注意事项

1. **依赖要求**：需要 `antd` 4.24+
2. **样式**：基于 Ant Design 原生样式，无需额外 CSS
3. **性能**：避免频繁调用 `reload()`，使用防抖优化
4. **类型安全**：使用 TypeScript 泛型确保类型安全

## 扩展

如需添加更多功能，可以在 `ProTable.tsx` 中扩展：

```tsx
// 添加批量操作
const [selectedRowKeys, setSelectedRowKeys] = useState<React.Key[]>([]);

<Table
  rowSelection={{
    selectedRowKeys,
    onChange: setSelectedRowKeys,
  }}
  // ...
/>;

// 添加导出功能
const handleExport = () => {
  // 导出逻辑
};
```

## 许可

MIT
