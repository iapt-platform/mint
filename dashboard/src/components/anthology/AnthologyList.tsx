import { ActionType, ProTable } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import { Link } from "react-router-dom";
import { message, Modal, Typography } from "antd";
import { PlusOutlined } from "@ant-design/icons";
import { Button, Dropdown, Popover } from "antd";
import {
  ExclamationCircleOutlined,
  TeamOutlined,
  DeleteOutlined,
} from "@ant-design/icons";

import AnthologyCreate from "../../components/anthology/AnthologyCreate";
import {
  IAnthologyListResponse,
  IDeleteResponse,
} from "../../components/api/Article";
import { delete_, get } from "../../request";
import { PublicityValueEnum } from "../../components/studio/table";
import { useRef, useState } from "react";

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
interface IWidget {
  studioName?: string;
  showCol?: string[];
  showCreate?: boolean;
  onTitleClick?: Function;
}
const Widget = ({
  studioName,
  showCol,
  showCreate = true,
  onTitleClick,
}: IWidget) => {
  const intl = useIntl();
  const [openCreate, setOpenCreate] = useState(false);

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
                    <Typography.Link
                      onClick={() => {
                        if (typeof onTitleClick !== "undefined") {
                          onTitleClick(row.id);
                        }
                      }}
                    >
                      {row.title}
                    </Typography.Link>
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
                      key: "open",
                      label: intl.formatMessage({
                        id: "buttons.open.in.library",
                      }),
                      icon: <TeamOutlined />,
                      disabled: true,
                    },
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
                      case "open":
                        window.open(`/anthology/${row.id}`, "_blank");
                        break;
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
                <Link to={`/anthology/${row.id}`} target="_blank">
                  {intl.formatMessage({
                    id: "buttons.view",
                  })}
                </Link>
              </Dropdown.Button>,
            ],
          },
        ]}
        request={async (params = {}, sorter, filter) => {
          // TODO
          console.log(params, sorter, filter);
          let url = `/v2/anthology?view=studio&name=${studioName}`;
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
          showCreate ? (
            <Popover
              content={
                <AnthologyCreate
                  studio={studioName}
                  onSuccess={() => {
                    setOpenCreate(false);
                    ref.current?.reload();
                  }}
                />
              }
              placement="bottomRight"
              trigger="click"
              open={openCreate}
              onOpenChange={(open: boolean) => {
                setOpenCreate(open);
              }}
            >
              <Button key="button" icon={<PlusOutlined />} type="primary">
                {intl.formatMessage({ id: "buttons.create" })}
              </Button>
            </Popover>
          ) : undefined,
        ]}
      />
    </>
  );
};

export default Widget;
