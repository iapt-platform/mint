import { useParams, Link } from "react-router-dom";
import { useIntl } from "react-intl";
import { Button, Popover, Typography, Dropdown, Modal, message } from "antd";
import { ActionType, ProTable } from "@ant-design/pro-components";
import {
  PlusOutlined,
  DeleteOutlined,
  ExclamationCircleOutlined,
} from "@ant-design/icons";

import { delete_, get } from "../../../request";
import { IGroupListResponse } from "../../../components/api/Group";
import GroupCreate from "../../../components/group/GroupCreate";
import { RoleValueEnum } from "../../../components/studio/table";
import { IDeleteResponse } from "../../../components/api/Article";
import { useRef, useState } from "react";

const { Text } = Typography;

interface DataItem {
  sn: number;
  id: string;
  name: string;
  description: string;
  role: string;
  createdAt: number;
}

const Widget = () => {
  const intl = useIntl(); //i18n
  const { studioname } = useParams(); //url 参数
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
        return delete_<IDeleteResponse>(`/v2/group/${id}`)
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
      <ProTable<DataItem>
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
              id: "forms.fields.name.label",
            }),
            dataIndex: "name",
            key: "name",
            tip: "过长会自动收缩",
            ellipsis: true,
            render: (text, row, index, action) => {
              return (
                <div key={index}>
                  <div>
                    <Link to={`/studio/${studioname}/group/${row.id}/show`}>
                      {row.name}
                    </Link>
                  </div>
                  <Text type="secondary">{row.description}</Text>
                </div>
              );
            },
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.role.label",
            }),
            dataIndex: "role",
            key: "role",
            width: 100,
            search: false,
            filters: true,
            onFilter: true,
            valueEnum: RoleValueEnum(),
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
                        showDeleteConfirm(row.id, row.name);
                        break;
                      default:
                        break;
                    }
                  },
                }}
              >
                <Link to={`/studio/${studioname}/group/${row.id}/edit`}>
                  {intl.formatMessage({
                    id: "buttons.edit",
                  })}
                </Link>
              </Dropdown.Button>,
            ],
          },
        ]}
        request={async (params = {}, sorter, filter) => {
          // TODO
          console.log(params, sorter, filter);
          let url = `/v2/group?view=studio&name=${studioname}`;
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);
          url += `&limit=${params.pageSize}&offset=${offset}`;
          if (typeof params.keyword !== "undefined") {
            url += "&search=" + (params.keyword ? params.keyword : "");
          }

          const res = await get<IGroupListResponse>(url);
          const items: DataItem[] = res.data.rows.map((item, id) => {
            const date = new Date(item.created_at);
            return {
              sn: id + 1,
              id: item.uid,
              name: item.name,
              description: item.description,
              role: item.role,
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
            content={
              <GroupCreate
                studio={studioname}
                onCreate={() => {
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
          </Popover>,
        ]}
      />
    </>
  );
};

export default Widget;
