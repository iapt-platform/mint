import { useIntl } from "react-intl";
import { Button, Popover, Dropdown, Modal, message } from "antd";
import { ProTable, type ActionType } from "@ant-design/pro-components";
import {
  PlusOutlined,
  DeleteOutlined,
  ExclamationCircleOutlined,
} from "@ant-design/icons";

import GroupCreate from "../../components/group/GroupCreate";

import { useCallback, useEffect, useRef, useState } from "react";

import StudioName from "../../components/auth/Studio";

import type { IStudio } from "../../api/Auth";
import { deleteGroup } from "../../api/group";
import { fetchMyGroupsPage } from "../../api/group-member";
import StatusBadge from "../../components/general/StatusBadge";
import { useAppDispatch } from "../../hooks";
import { refreshMyGroups } from "../teamspace/refreshMyGroups";

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
  onSelect?: (id: string) => void;
  onSetting?: (id: string) => void;
}
/** 我的团队：「我创建的」与「我参加的」两个标签页（v3 /me/groups 的 owned 过滤） */
const GroupList = ({ onSelect, onSetting }: IWidget) => {
  const intl = useIntl(); //i18n
  const dispatch = useAppDispatch();

  const [openCreate, setOpenCreate] = useState(false);
  const [activeKey, setActiveKey] = useState<React.Key | undefined>("my");
  const [myNumber, setMyNumber] = useState<number>(0);
  const [collaborationNumber, setCollaborationNumber] = useState<number>(0);

  /** 两个标签页的数量：各取一条，读 meta.total */
  const refreshNumbers = useCallback(() => {
    fetchMyGroupsPage({ owned: true, per_page: 1 }).then((res) =>
      setMyNumber(res.meta?.total ?? 0)
    );
    fetchMyGroupsPage({ owned: false, per_page: 1 }).then((res) =>
      setCollaborationNumber(res.meta?.total ?? 0)
    );
  }, []);
  useEffect(refreshNumbers, [refreshNumbers]);

  /** 建、删团队后：刷新列表、数量，以及头像弹窗里的账户列表 */
  const afterChange = () => {
    ref.current?.reload();
    refreshNumbers();
    refreshMyGroups(dispatch).catch(() => undefined);
  };

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
      async onOk() {
        // 只有 owner 能删；名下还有资源时后端 409，原因由 unwrap 弹出
        await deleteGroup(id);
        message.success(intl.formatMessage({ id: "flashes.success" }));
        afterChange();
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
            valueEnum: Object.fromEntries(
              ["owner", "manager", "editor", "reader"].map((role) => [
                role,
                {
                  text: intl.formatMessage({
                    id: `labels.group.role.${role}`,
                  }),
                },
              ])
            ),
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
        request={async (params = {}) => {
          const page = params.current ?? 1;
          const perPage = params.pageSize ?? 20;
          try {
            const res = await fetchMyGroupsPage({
              owned: activeKey === "my",
              search: params.keyword || undefined,
              page,
              per_page: perPage,
            });
            const items: DataItem[] = (res.data ?? []).map((item, index) => ({
              sn: (page - 1) * perPage + index + 1,
              id: item.id ?? "",
              name: item.name ?? "",
              description: item.description ?? "",
              role: item.role ?? "",
              created_at: item.created_at ?? "",
              studio: item.owner
                ? {
                    id: item.owner.id ?? "",
                    nickName: item.owner.nickName,
                    realName: item.owner.realName,
                    studioName: item.owner.userName,
                    avatar: item.owner.avatar ?? undefined,
                  }
                : undefined,
            }));
            return { total: res.meta?.total ?? 0, success: true, data: items };
          } catch {
            return { total: 0, success: false, data: [] };
          }
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
                onCreate={() => {
                  setOpenCreate(false);
                  afterChange();
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
