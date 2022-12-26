import { useIntl } from "react-intl";
import { useState } from "react";
import { ProList } from "@ant-design/pro-components";
import { UserAddOutlined } from "@ant-design/icons";
import { Space, Tag, Button, Layout } from "antd";
import GroupAddMember from "./AddMember";

const { Content } = Layout;

const defaultData = [
  {
    id: "1",
    name: "小僧善巧",
    tag: [{ title: "管理员", color: "success" }],
    image:
      "https://gw.alipayobjects.com/zos/antfincdn/efFD%24IOql2/weixintupian_20170331104822.jpg",
  },
  {
    id: "2",
    name: "无语",
    tag: [{ title: "管理员", color: "success" }],
    image:
      "https://gw.alipayobjects.com/zos/antfincdn/efFD%24IOql2/weixintupian_20170331104822.jpg",
  },
  {
    id: "3",
    name: "慧欣",
    tag: [],
    image:
      "https://gw.alipayobjects.com/zos/antfincdn/efFD%24IOql2/weixintupian_20170331104822.jpg",
  },
  {
    id: "4",
    name: "谭博文",
    tag: [],
    image:
      "https://gw.alipayobjects.com/zos/antfincdn/efFD%24IOql2/weixintupian_20170331104822.jpg",
  },
  {
    id: "4",
    name: "豆沙猫",
    tag: [],
    image:
      "https://gw.alipayobjects.com/zos/antfincdn/efFD%24IOql2/weixintupian_20170331104822.jpg",
  },
  {
    id: "4",
    name: "visuddhinanda",
    tag: [],
    image:
      "https://gw.alipayobjects.com/zos/antfincdn/efFD%24IOql2/weixintupian_20170331104822.jpg",
  },
];
type DataItem = typeof defaultData[number];
interface IWidgetGroupFile {
  groupId?: string;
}
const Widget = ({ groupId }: IWidgetGroupFile) => {
  const intl = useIntl(); //i18n
  const [dataSource, setDataSource] = useState<DataItem[]>(defaultData);

  return (
    <Content>
      <Space>{groupId}</Space>
      <ProList<DataItem>
        rowKey="id"
        headerTitle={intl.formatMessage({ id: "group.member" })}
        toolBarRender={() => {
          return [<GroupAddMember groupId={groupId} />];
        }}
        dataSource={dataSource}
        showActions="hover"
        onDataSourceChange={setDataSource}
        metas={{
          title: {
            dataIndex: "name",
          },
          avatar: {
            dataIndex: "image",
            editable: false,
          },
          subTitle: {
            render: (text, row, index, action) => {
              const showtag = row.tag.map((item, id) => {
                return (
                  <Tag color={item.color} key={id}>
                    {item.title}
                  </Tag>
                );
              });
              return <Space size={0}>{showtag}</Space>;
            },
          },
          actions: {
            render: (text, row, index, action) => [
              <Button
                style={{ padding: 0, margin: 0 }}
                type="link"
                danger
                onClick={() => {
                  action?.startEditable(row.id);
                }}
                key="link"
              >
                {intl.formatMessage({ id: "buttons.remove" })}
              </Button>,
            ],
          },
        }}
      />
    </Content>
  );
};

export default Widget;
