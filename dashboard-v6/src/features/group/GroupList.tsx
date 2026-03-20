import { useIntl } from "react-intl";
import { Button, Popover, Dropdown, Modal, message } from "antd";
import { ProTable, type ActionType } from "@ant-design/pro-components";
import {
  PlusOutlined,
  DeleteOutlined,
  ExclamationCircleOutlined,
} from "@ant-design/icons";

import { delete_, get } from "../../request";

import GroupCreate from "../../components/group/GroupCreate";
import { RoleValueEnum } from "../../components/studio/table";

import { useEffect, useRef, useState } from "react";

import StudioName from "../../components/auth/Studio";

import { getSorterUrl } from "../../utils";
import type { IStudio } from "../../api/Auth";
import type { IDeleteResponse, IGroupListResponse } from "../../api/group";
import StatusBadge from "../../components/general/StatusBadge";

interface IMyNumberResponse {
  ok: boolean;
  message: string;
  data: {
    my: number;
    collaboration: number;
  };
}

interface DataItem {
  sn: number;
  id: string;
  name: string;
  description: string;
  role: string;
  created_at: string;
  studio?: IStudio;
}

interface IWidget {
  studioName?: string;
  onSelect?: (id: string) => void;
  onSetting?: (id: string) => void;
}
const GroupList = ({ studioName, onSelect, onSetting }: IWidget) => {
  const intl = useIntl(); //i18n

  const [openCreate, setOpenCreate] = useState(false);
  const [activeKey, setActiveKey] = useState<React.Key | undefined>("my");
  const [myNumber, setMyNumber] = useState<number>(0);
  const [collaborationNumber, setCollaborationNumber] = useState<number>(0);

  useEffect(() => {
    /**
     * 获取各种课程的数量
     */
    const url = `/api/v2/group-my-number?studio=${studioName}`;
    console.log("url", url);
    get<IMyNumberResponse>(url).then((json) => {
      if (json.ok) {
        setMyNumber(json.data.my);
        setCollaborationNumber(json.data.collaboration);
      }
    });
  }, [studioName]);

  const showDeleteConfirm = (id: string, title: string) => {
    Modal.confirm({
      icon: <ExclamationCircleOutlined />,
      title:
        intl.formatMessage({
          id: "message.delete.confirm",
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
        return delete_<IDeleteResponse>(`/api/v2/group/${id}`)
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

  const ref = useRef<ActionType | null>(null);

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
            tooltip: "过长会自动收缩",
            ellipsis: true,
            render: (_, row) => {
              return (
                <Button type="link" onClick={() => onSelect?.(row.id)}>
                  {row.name}
                </Button>
              );
            },
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.owner.label",
            }),
            key: "owner",
            search: false,
            render: (_, row) => {
              return <StudioName data={row.studio} />;
            },
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.description.label",
            }),
            dataIndex: "description",
            key: "description",
            search: false,
            tooltip: "过长会自动收缩",
            ellipsis: true,
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
            key: "created_at",
            width: 100,
            search: false,
            dataIndex: "created_at",
            valueType: "date",
            sorter: true,
          },
          {
            title: intl.formatMessage({ id: "buttons.option" }),
            key: "option",
            width: 120,
            valueType: "option",
            render: (_, row, index) => [
              <Dropdown.Button
                key={index}
                type="link"
                menu={{
                  items: [
                    {
                      key: "remove",
                      label: intl.formatMessage({
                        id: "buttons.delete",
                      }),
                      icon: <DeleteOutlined />,
                      danger: true,
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
                <Button type="link" onClick={() => onSetting?.(row.id)}>
                  {intl.formatMessage({
                    id: "buttons.setting",
                  })}
                </Button>
              </Dropdown.Button>,
            ],
          },
        ]}
        request={async (params = {}, sorter, filter) => {
          console.log(params, sorter, filter);
          let url = `/api/v2/group?view=studio&name=${studioName}&view2=${activeKey}`;
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);
          url += `&limit=${params.pageSize}&offset=${offset}`;
          url += params.keyword ? "&search=" + params.keyword : "";
          url += getSorterUrl(sorter);

          console.log(url);
          const res = await get<IGroupListResponse>(url);
          const items: DataItem[] = res.data.rows.map((item, id) => {
            return {
              sn: id + 1,
              id: item.uid,
              name: item.name,
              description: item.description,
              role: item.role,
              created_at: item.created_at,
              studio: item.studio,
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
                studio={studioName}
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
        toolbar={{
          menu: {
            activeKey,
            items: [
              {
                key: "my",
                label: (
                  <span>
                    {intl.formatMessage({ id: "labels.this-studio" })}
                    <StatusBadge count={myNumber} active={activeKey === "my"} />
                  </span>
                ),
              },
              {
                key: "collaboration",
                label: (
                  <span>
                    {intl.formatMessage({ id: "labels.collaboration" })}
                    <StatusBadge
                      count={collaborationNumber}
                      active={activeKey === "collaboration"}
                    />
                  </span>
                ),
              },
            ],
            onChange(key) {
              console.log("show course", key);
              setActiveKey(key);
              ref.current?.reload();
            },
          },
        }}
      />
    </>
  );
};

export default GroupList;
