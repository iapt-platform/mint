//选择讲师组件

import { useIntl } from "react-intl";
import { useState } from "react";
import { ProList } from "@ant-design/pro-components";
import { Button, Layout } from "antd";
import AddLesson from "./AddLesson";

const { Content } = Layout;

const defaultData = [
  {
    id: "1",
    name: "lesson0",
    //tag: [{ title: "管理员", color: "success" }],
    //image:
    //  "https://gw.alipayobjects.com/zos/antfincdn/efFD%24IOql2/weixintupian_20170331104822.jpg",
  },
];
type DataItem = (typeof defaultData)[number];
interface IWidgetGroupFile {
  groupId?: string;
}
const LessonSelectWidget = ({ groupId }: IWidgetGroupFile) => {
  const intl = useIntl(); //i18n
  const [dataSource, setDataSource] = useState<DataItem[]>(defaultData);

  return (
    <Content>
      <ProList<DataItem>
        rowKey="id"
        headerTitle={intl.formatMessage({ id: "forms.fields.lesson.label" })}
        toolBarRender={() => {
          return [<AddLesson groupId={groupId} />];
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
          // subTitle: {
          //   render: (text, row, index, action) => {
          //     const showtag = row.tag.map((item, id) => {
          //       return (
          //         <Tag color={item.color} key={id}>
          //           {item.title}
          //         </Tag>
          //       );
          //     });
          //     return <Space size={0}>{showtag}</Space>;
          //   },
          // },
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

export default LessonSelectWidget;
