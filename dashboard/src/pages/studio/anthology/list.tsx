import { useParams } from "react-router-dom";
import { ActionType, ProTable } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import { Link } from "react-router-dom";
import { message, Modal, Space, Table, Typography } from "antd";
import { PlusOutlined } from "@ant-design/icons";
import { Button, Dropdown, Popover } from "antd";
import {
  ExclamationCircleOutlined,
  TeamOutlined,
  DeleteOutlined,
} from "@ant-design/icons";

import AnthologyCreate from "../../../components/anthology/AnthologyCreate";
import {
  IAnthologyListResponse,
  IDeleteResponse,
} from "../../../components/api/Article";
import { delete_, get } from "../../../request";
import { PublicityValueEnum } from "../../../components/studio/table";
import { useRef } from "react";

const { Text } = Typography;

interface IItem {
  sn: number;
  id: string;
  title: string;
  subtitle: string;
  publicity: number;
  articles: number;
  createdAt: number;
}

const Widget = () => {
  const intl = useIntl();
  const { studioname } = useParams();
  const anthologyCreate = <AnthologyCreate studio={studioname} />;
  const showDeleteConfirm = (id: string, title: string) => {
    Modal.confirm({
      icon: <ExclamationCircleOutlined />,
      title:
        intl.formatMessage({
          id: "message.delete.sure",
        }) +
        intl.formatMessage({
          id: "message.irrevocable",
        }),

      content: title,
      okText: intl.formatMessage({
        id: "buttons.delete",
      }),
      okType: "danger",
      cancelText: intl.formatMessage({
        id: "buttons.no",
      }),
      onOk() {
        console.log("delete", id);
        return delete_<IDeleteResponse>(`/v2/anthology/${id}`)
          .then((json) => {
            if (json.ok) {
              message.success("删除成功");
              ref.current?.reload();
            } else {
              message.error(json.message);
            }
          })
          .catch((e) => console.log("Oops errors!", e));
      },
    });
  };
  const ref = useRef<ActionType>();
  return (
    <>
      <ProTable<IItem>
        actionRef={ref}
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
                <div key={index}>
                  <div>
                    <Link to={`/anthology/${row.id}`} target="_blank">
                      {row.title}
                    </Link>
                  </div>
                  <Text type="secondary">{row.subtitle}</Text>
                </div>
              );
            },
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.publicity.label",
            }),
            dataIndex: "publicity",
            key: "publicity",
            width: 100,
            search: false,
            filters: true,
            onFilter: true,
            valueEnum: PublicityValueEnum(),
          },
          {
            title: intl.formatMessage({
              id: "article.fields.article.count.label",
            }),
            dataIndex: "articles",
            key: "articles",
            width: 100,
            search: false,
            sorter: (a, b) => a.articles - b.articles,
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
            render: (text, row, index, action) => [
              <Dropdown.Button
                key={index}
                type="link"
                menu={{
                  items: [
                    {
                      key: "share",
                      label: intl.formatMessage({
                        id: "buttons.share",
                      }),
                      icon: <TeamOutlined />,
                      disabled: true,
                    },
                    {
                      key: "remove",
                      label: (
                        <Text type="danger">
                          {intl.formatMessage({
                            id: "buttons.delete",
                          })}
                        </Text>
                      ),
                      icon: (
                        <Text type="danger">
                          <DeleteOutlined />
                        </Text>
                      ),
                    },
                  ],
                  onClick: (e) => {
                    switch (e.key) {
                      case "share":
                        break;
                      case "remove":
                        showDeleteConfirm(row.id, row.title);
                        break;
                      default:
                        break;
                    }
                  },
                }}
              >
                <Link to={`/studio/${studioname}/anthology/${row.id}/edit`}>
                  {intl.formatMessage({
                    id: "buttons.edit",
                  })}
                </Link>
              </Dropdown.Button>,
            ],
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
              {intl.formatMessage({ id: "buttons.selected" })}{" "}
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
              <Button type="link">批量删除</Button>
            </Space>
          );
        }}
        request={async (params = {}, sorter, filter) => {
          // TODO
          console.log(params, sorter, filter);
          let url = `/v2/anthology?view=studio&name=${studioname}`;
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);
          url += `&limit=${params.pageSize}&offset=${offset}`;
          if (typeof params.keyword !== "undefined") {
            url += "&search=" + (params.keyword ? params.keyword : "");
          }

          const res = await get<IAnthologyListResponse>(url);
          const items: IItem[] = res.data.rows.map((item, id) => {
            const date = new Date(item.created_at);
            return {
              sn: id + 1,
              id: item.uid,
              title: item.title,
              subtitle: item.subtitle,
              publicity: item.status,
              articles: item.childrenNumber,
              createdAt: date.getTime(),
            };
          });
          console.log(items);
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
        toolBarRender={() => [
          <Popover
            content={anthologyCreate}
            placement="bottomRight"
            trigger="click"
          >
            <Button key="button" icon={<PlusOutlined />} type="primary">
              {intl.formatMessage({ id: "buttons.create" })}
            </Button>
          </Popover>,
        ]}
      />
    </>
  );
};

export default Widget;
