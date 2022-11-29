import { ProList } from "@ant-design/pro-components";
import { Button, Progress } from "antd";
import { useState } from "react";

const dataSource = [
  {
    name: "语雀的天空",
    description: "简介",
    type: "translation",
    role: "owner",
    source: "studio",
  },
  {
    name: "Ant Design",
    description: "简介",
    type: "translation",
    role: "reader",
    source: "studio",
  },
  {
    name: "蚂蚁金服体验科技",
    description: "简介",
    type: "nissaya",
    role: "reader",
    source: "collaborate",
  },
  {
    name: "TechUI",
    description: "简介",
    type: "nissaya",
    role: "editor",
    source: "public",
  },
];
interface IWidget {
  multiSelect?: boolean;
}
const Widget = ({ multiSelect = false }: IWidget) => {
  const [selectedRowKeys, setSelectedRowKeys] = useState<React.Key[]>([]);
  const rowSelection = {
    selectedRowKeys,
    onChange: (keys: React.Key[]) => setSelectedRowKeys(keys),
  };

  return (
    <ProList<{ title: string }>
      toolBarRender={() => {
        return [
          <Button key="3" type="primary">
            新建
          </Button>,
        ];
      }}
      metas={{
        title: {
          dataIndex: "name",
          title: "版本名称",
        },
        description: { dataIndex: "description" },
        extra: {
          render: () => (
            <div
              style={{
                minWidth: 200,
                flex: 1,
                display: "flex",
                justifyContent: "flex-end",
              }}
            >
              <div
                style={{
                  width: "200px",
                }}
              >
                <Progress percent={80} />
              </div>
            </div>
          ),
        },
        actions: {
          render: () => {
            return [<a key="init">邀请</a>, "发布"];
          },
        },
      }}
      rowKey="title"
      headerTitle="选择版本"
      rowSelection={rowSelection}
      dataSource={dataSource}
    />
  );
};

export default Widget;
