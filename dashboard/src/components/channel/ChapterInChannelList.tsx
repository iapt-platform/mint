import { useIntl } from "react-intl";
import { Progress, Typography } from "antd";
import { ProTable } from "@ant-design/pro-components";
import { Link } from "react-router-dom";
import { Button, Dropdown } from "antd";
import { DeleteOutlined } from "@ant-design/icons";

import { get } from "../../request";

import { IChapterListResponse } from "../../components/api/Corpus";
import { IArticleParam } from "../../pages/studio/recent/list";

const { Text } = Typography;

interface IItem {
  sn: number;
  title: string;
  subTitle: string;
  summary: string;
  book: number;
  paragraph: number;
  path: string;
  progress: number;
  view: number;
  created_at: string;
  updated_at: string;
}
interface IWidget {
  channelId?: string;
  onSelect?: Function;
}
const ChapterInChannelListWidget = ({ channelId, onSelect }: IWidget) => {
  const intl = useIntl();

  return (
    <ProTable<IItem>
      columns={[
        {
          title: intl.formatMessage({
            id: "dict.fields.sn.label",
          }),
          dataIndex: "sn",
          key: "sn",
          width: 50,
          search: false,
        },
        {
          title: intl.formatMessage({
            id: "forms.fields.title.label",
          }),
          dataIndex: "title",
          key: "title",
          tooltip: "过长会自动收缩",
          ellipsis: true,
          render: (text, row, index, action) => {
            return (
              <div key={index}>
                <div key={1}>
                  <Button
                    type="link"
                    onClick={(event) => {
                      if (typeof onSelect !== "undefined") {
                        const chapter: IArticleParam = {
                          type: "chapter",
                          articleId: `${row.book}-${row.paragraph}`,
                          mode: "read",
                          channelId: channelId,
                        };
                        onSelect(event, chapter);
                      }
                    }}
                  >
                    {row.title ? row.title : row.subTitle}
                  </Button>
                </div>
                <Text type="secondary" key={2}>
                  {row.subTitle}
                </Text>
              </div>
            );
          },
        },
        {
          title: intl.formatMessage({
            id: "forms.fields.summary.label",
          }),
          dataIndex: "summary",
          key: "summary",
          tooltip: "过长会自动收缩",
          ellipsis: true,
        },
        {
          title: intl.formatMessage({
            id: "forms.fields.publicity.label",
          }),
          dataIndex: "progress",
          key: "progress",
          width: 100,
          search: false,
          render: (text, row, index, action) => {
            const per = Math.round(row.progress * 100);
            return <Progress percent={per} size="small" key={index} />;
          },
        },
        {
          title: intl.formatMessage({
            id: "forms.fields.publicity.label",
          }),
          dataIndex: "view",
          key: "view",
          width: 100,
          search: false,
        },
        {
          title: intl.formatMessage({
            id: "forms.fields.created-at.label",
          }),
          key: "created-at",
          width: 100,
          search: false,
          dataIndex: "created_at",
          valueType: "date",
          sorter: false,
        },
        {
          title: intl.formatMessage({ id: "buttons.option" }),
          key: "option",
          width: 120,
          valueType: "option",
          render: (text, row, index, action) => {
            let editLink = `/article/chapter/${row.book}-${row.paragraph}?mode=edit`;
            editLink += channelId ? `&channel=${channelId}` : "";
            return [
              <Dropdown.Button
                key={index}
                type="link"
                menu={{
                  items: [
                    {
                      key: "remove",
                      disabled: true,
                      danger: true,
                      label: intl.formatMessage({
                        id: "buttons.delete",
                      }),
                      icon: <DeleteOutlined />,
                    },
                  ],
                  onClick: (e) => {
                    switch (e.key) {
                      case "remove":
                        break;
                      default:
                        break;
                    }
                  },
                }}
              >
                <Link to={editLink}>
                  {intl.formatMessage({
                    id: "buttons.translate",
                  })}
                </Link>
              </Dropdown.Button>,
            ];
          },
        },
      ]}
      request={async (params = {}, sorter, filter) => {
        // TODO 加排序
        console.log(params, sorter, filter);
        const offset = ((params.current || 1) - 1) * (params.pageSize || 20);
        const res = await get<IChapterListResponse>(
          `/v2/progress?view=chapter&channel=${channelId}&progress=0.01&offset=${offset}`
        );
        console.log(res.data.rows);
        const items: IItem[] = res.data.rows.map((item, id) => {
          return {
            sn: id + offset + 1,
            book: item.book,
            paragraph: item.para,
            view: item.view,
            title: item.title,
            subTitle: item.toc,
            summary: item.summary,
            path: item.path,
            progress: item.progress,
            created_at: item.created_at,
            updated_at: item.updated_at,
          };
        });
        return {
          total: res.data.count,
          succcess: true,
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
    />
  );
};

export default ChapterInChannelListWidget;
