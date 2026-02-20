// 使用示例：如何替换你的 ChannelTableWidget 组件

// 1. 替换导入语句
// 原来：
// import { ActionType, ProTable } from "@ant-design/pro-components";

// 现在：

// 2. 其他代码保持不变，ProTable 组件会自动适配你的用法

// 主要实现的功能：
// ✅ actionRef - 通过 ref.current?.reload() 刷新表格
// ✅ columns 配置 - 支持所有你使用的列配置
// ✅ request 异步请求 - 支持分页、排序、过滤参数
// ✅ rowKey - 行唯一标识
// ✅ bordered - 边框样式
// ✅ pagination - 分页配置（showQuickJumper, showSizeChanger）
// ✅ search - 搜索功能（options.search）
// ✅ toolBarRender - 工具栏渲染
// ✅ toolbar.menu - 标签页切换（my/collaboration/community）
// ✅ valueEnum - 枚举值过滤
// ✅ valueType - 日期格式化
// ✅ sorter - 排序支持
// ✅ filters/onFilter - 过滤支持
// ✅ ellipsis - 文本溢出省略
// ✅ hideInTable - 隐藏列

// 完整替换示例：
/*
import ProTable, { ActionType } from './ProTable';
import { FormattedMessage, useIntl } from "react-intl";
// ... 其他导入保持不变

const ChannelTableWidget = ({ ... }) => {
  const ref = useRef<ActionType | null>(null);
  
  return (
    <ProTable<IChannelItem>
      actionRef={ref}
      columns={[ ... ]} // 你的列配置保持不变
      request={async (params, sorter, filter) => {
        // 你的请求逻辑保持不变
        // ...
        return {
          total: res.data.count,
          success: true,
          data: items,
        };
      }}
      rowKey="id"
      bordered
      pagination={{
        showQuickJumper: true,
        showSizeChanger: true,
      }}
      search={false}
      options={{
        search: true,
      }}
      toolBarRender={() => [ ... ]} // 保持不变
      toolbar={{
        menu: {
          activeKey,
          items: [ ... ],
          onChange(key) { ... }
        }
      }}
    />
  );
};
*/
