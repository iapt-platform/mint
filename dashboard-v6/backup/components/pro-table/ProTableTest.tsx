import React, { useRef, useState } from "react";
import { Button, Badge, message } from "antd";
import { PlusOutlined } from "@ant-design/icons";
import ProTable, { type ActionType } from "./ProTable";

// 模拟数据类型
interface IChannelItem {
  id: number;
  uid: string;
  title: string;
  summary: string;
  type: "translation" | "nissaya" | "commentary" | "original";
  role?: "owner" | "manager" | "editor" | "member";
  publicity: number;
  created_at: string;
}

// 模拟 API 响应
const mockApiResponse = (page: number, pageSize: number, keyword: string) => {
  const mockData: IChannelItem[] = Array.from({ length: 50 }, (_, i) => ({
    id: i + 1,
    uid: `channel-${i + 1}`,
    title: `Channel ${i + 1} ${keyword ? `(含关键词: ${keyword})` : ""}`,
    summary: `这是第 ${i + 1} 个频道的简介`,
    type: ["translation", "nissaya", "commentary", "original"][i % 4] as any,
    role: ["owner", "manager", "editor", "member"][i % 4] as any,
    publicity: i % 3,
    created_at: new Date(2024, 0, i + 1).toISOString(),
  }));

  const start = (page - 1) * pageSize;
  const end = start + pageSize;

  // 模拟关键词搜索
  let filtered = mockData;
  if (keyword) {
    filtered = mockData.filter((item) =>
      item.title.toLowerCase().includes(keyword.toLowerCase())
    );
  }

  return {
    data: {
      rows: filtered.slice(start, end),
      count: filtered.length,
    },
  };
};

// 测试组件
const ProTableTest: React.FC = () => {
  const ref = useRef<ActionType | null>(null);
  const [activeKey, setActiveKey] = useState<React.Key>("my");
  const [myNumber] = useState(25);
  const [collaborationNumber] = useState(15);

  const renderBadge = (count: number, active = false) => {
    return (
      <Badge
        count={count}
        style={{
          marginBlockStart: -2,
          marginInlineStart: 4,
          color: active ? "#1890FF" : "#999",
          backgroundColor: active ? "#E6F7FF" : "#eee",
        }}
      />
    );
  };

  return (
    <div style={{ padding: 24 }}>
      <h1>ProTable 测试示例</h1>

      <ProTable<IChannelItem>
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
            width: 250,
            key: "title",
            tooltip: "过长会自动收缩",
            ellipsis: true,
            render: (_text, row, _index, _action) => {
              return (
                <Button
                  type="link"
                  onClick={() => {
                    message.info(`点击了: ${row.title}`);
                    console.log("选中的频道:", row);
                  }}
                >
                  {row.title}
                </Button>
              );
            },
          },
          {
            title: "简介",
            dataIndex: "summary",
            key: "summary",
            tooltip: "过长会自动收缩",
            ellipsis: true,
          },
          {
            title: "角色",
            dataIndex: "role",
            key: "role",
            width: 80,
            search: false,
            filters: true,
            onFilter: true,
            valueEnum: {
              all: { text: "全部", status: "Default" },
              owner: { text: "所有者" },
              manager: { text: "管理员" },
              editor: { text: "编辑" },
              member: { text: "成员" },
            },
          },
          {
            title: "类型",
            dataIndex: "type",
            key: "type",
            width: 80,
            search: false,
            filters: true,
            onFilter: true,
            valueEnum: {
              all: { text: "全部", status: "Default" },
              translation: { text: "翻译", status: "Success" },
              nissaya: { text: "Nissaya", status: "Processing" },
              commentary: { text: "注释", status: "Default" },
              original: { text: "原创", status: "Default" },
            },
          },
          {
            title: "公开性",
            dataIndex: "publicity",
            key: "publicity",
            width: 80,
            search: false,
            filters: true,
            onFilter: true,
            valueEnum: {
              0: { text: "私有" },
              1: { text: "组内" },
              2: { text: "公开" },
            },
          },
          {
            title: "创建时间",
            key: "created_at",
            width: 120,
            search: false,
            dataIndex: "created_at",
            valueType: "date",
            sorter: true,
          },
          {
            title: "操作",
            key: "option",
            width: 120,
            valueType: "option",
            hideInTable: activeKey !== "my",
            render: (_text, row, _index, action) => {
              return [
                <Button
                  key="edit"
                  type="link"
                  onClick={() => message.info(`编辑: ${row.title}`)}
                >
                  编辑
                </Button>,
                <Button
                  key="delete"
                  type="link"
                  danger
                  onClick={() => {
                    message.success(`删除成功: ${row.title}`);
                    action.reload();
                  }}
                >
                  删除
                </Button>,
              ];
            },
          },
        ]}
        request={async (params = {}, sorter, filter) => {
          console.log("请求参数:", { params, sorter, filter });

          // 模拟网络延迟
          await new Promise((resolve) => setTimeout(resolve, 500));

          const page = params.current || 1;
          const pageSize = params.pageSize || 20;
          const keyword = params.keyword || "";

          const res = mockApiResponse(page, pageSize, keyword);

          return {
            total: res.data.count,
            success: true,
            data: res.data.rows.map((item, id) => ({
              ...item,
              id: (page - 1) * pageSize + id + 1,
            })),
          };
        }}
        rowKey="uid"
        bordered
        pagination={{
          showQuickJumper: true,
          showSizeChanger: true,
          defaultPageSize: 10,
        }}
        search={false}
        options={{
          search: true,
        }}
        toolBarRender={() => [
          <Button
            key="refresh"
            onClick={() => {
              ref.current?.reload();
              message.success("刷新成功");
            }}
          >
            刷新
          </Button>,
          <Button
            key="reset"
            onClick={() => {
              ref.current?.reset();
              message.success("重置成功");
            }}
          >
            重置
          </Button>,
          <Button
            key="create"
            icon={<PlusOutlined />}
            type="primary"
            onClick={() => message.info("创建新频道")}
          >
            创建
          </Button>,
        ]}
        toolbar={{
          menu: {
            activeKey,
            items: [
              {
                key: "my",
                label: (
                  <span>
                    我的工作室
                    {renderBadge(myNumber, activeKey === "my")}
                  </span>
                ),
              },
              {
                key: "collaboration",
                label: (
                  <span>
                    协作
                    {renderBadge(
                      collaborationNumber,
                      activeKey === "collaboration"
                    )}
                  </span>
                ),
              },
              {
                key: "community",
                label: (
                  <span>
                    社区
                    {renderBadge(10, activeKey === "community")}
                  </span>
                ),
              },
            ],
            onChange(key) {
              console.log("切换标签:", key);
              setActiveKey(key);
              ref.current?.reload();
            },
          },
        }}
        headerTitle="频道列表"
      />

      <div style={{ marginTop: 24, padding: 16, background: "#f5f5f5" }}>
        <h3>测试功能：</h3>
        <ul>
          <li>✅ 切换标签页（我的工作室/协作/社区）</li>
          <li>✅ 搜索功能（输入关键词搜索）</li>
          <li>✅ 分页（切换页码、调整每页数量）</li>
          <li>✅ 排序（点击"创建时间"列头排序）</li>
          <li>✅ 过滤（点击"角色"、"类型"、"公开性"列头过滤）</li>
          <li>✅ 刷新按钮（手动刷新表格）</li>
          <li>✅ 重置按钮（重置所有过滤和排序）</li>
          <li>✅ 操作列（仅在"我的工作室"显示）</li>
          <li>✅ 文本省略（标题和简介过长自动省略）</li>
          <li>✅ 日期格式化（创建时间自动格式化）</li>
        </ul>
      </div>
    </div>
  );
};

export default ProTableTest;
