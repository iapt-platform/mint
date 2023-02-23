import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import { useParams } from "react-router-dom";
import { Card, Progress, Typography } from "antd";
import { ProTable } from "@ant-design/pro-components";
import { Link } from "react-router-dom";
import { Space, Table } from "antd";
import type { MenuProps } from "antd";
import { Button, Dropdown, Menu } from "antd";
import { SearchOutlined } from "@ant-design/icons";

import { get } from "../../../request";

import GoBack from "../../../components/studio/GoBack";
import { IChapterListResponse } from "../../../components/api/Corpus";
import { IApiResponseChannel } from "../../../components/api/Channel";
const { Text } = Typography;

const onMenuClick: MenuProps["onClick"] = (e) => {
  console.log("click", e);
};

const menu = (
  <Menu
    onClick={onMenuClick}
    items={[
      {
        key: "share",
        label: "分享",
        icon: <SearchOutlined />,
      },
      {
        key: "delete",
        label: "删除",
        icon: <SearchOutlined />,
      },
    ]}
  />
);

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
  createdAt: number;
  updatedAt: number;
}
const Widget = () => {
  const intl = useIntl();
  const { channelId } = useParams(); //url 参数
  const { studioname } = useParams();
  const [title, setTitle] = useState("");

  useEffect(() => {
    get<IApiResponseChannel>(`/v2/channel/${channelId}`).then((json) => {
      setTitle(json.data.name);
    });
  }, [channelId]);
  return (
    <Card
      title={<GoBack to={`/studio/${studioname}/channel/list`} title={title} />}
    >
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
            tip: "过长会自动收缩",
            ellipsis: true,
            render: (text, row, index, action) => {
              return (
                <div>
                  <div>
                    <Link
                      to={`/article/chapter/${row.book}-${row.paragraph}_${channelId}`}
                    >
                      {row.title ? row.title : row.subTitle}
                    </Link>
                  </div>
                  <Text type="secondary">{row.subTitle}</Text>
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
            tip: "过长会自动收缩",
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
              return <Progress percent={per} size="small" />;
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
            dataIndex: "createdAt",
            valueType: "date",
            sorter: (a, b) => a.createdAt - b.createdAt,
          },
          {
            title: intl.formatMessage({ id: "buttons.option" }),
            key: "option",
            width: 120,
            valueType: "option",
            render: (text, row, index, action) => {
              return [
                <Dropdown.Button key={index} type="link" overlay={menu}>
                  <Link
                    to={`/article/chapter/${row.book}-${row.paragraph}_${channelId}/edit`}
                  >
                    {intl.formatMessage({
                      id: "buttons.edit",
                    })}
                  </Link>
                </Dropdown.Button>,
              ];
            },
          },
        ]}
        rowSelection={{
          // 自定义选择项参考: https://ant.design/components/table-cn/#components-table-demo-row-selection-custom
          // 注释该行则默认不显示下拉选项
          selections: [Table.SELECTION_ALL, Table.SELECTION_INVERT],
        }}
        tableAlertRender={({
          selectedRowKeys,
          selectedRows,
          onCleanSelected,
        }) => (
          <Space size={24}>
            <span>
              {intl.formatMessage({ id: "buttons.selected" })}
              {selectedRowKeys.length}
              <Button
                type="link"
                style={{ marginInlineStart: 8 }}
                onClick={onCleanSelected}
              >
                {intl.formatMessage({ id: "buttons.unselect" })}
              </Button>
            </span>
          </Space>
        )}
        tableAlertOptionRender={() => {
          return (
            <Space size={16}>
              <Button type="link">
                {intl.formatMessage({
                  id: "buttons.delete.all",
                })}
              </Button>
            </Space>
          );
        }}
        request={async (params = {}, sorter, filter) => {
          // TODO
          console.log(params, sorter, filter);
          const offset = (params.current || 1 - 1) * (params.pageSize || 20);
          const res = await get<IChapterListResponse>(
            `/v2/progress?view=chapter&channel=${channelId}&progress=0.01&offset=${offset}`
          );
          console.log(res.data.rows);
          const items: IItem[] = res.data.rows.map((item, id) => {
            const createdAt = new Date(item.created_at);
            const updatedAt = new Date(item.updated_at);
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
              createdAt: createdAt.getTime(),
              updatedAt: updatedAt.getTime(),
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
    </Card>
  );
};

export default Widget;
