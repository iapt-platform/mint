import { useIntl } from "react-intl";
import { useRef, useState } from "react";
import { type ActionType, ProList } from "@ant-design/pro-components";
import { Tag, Button, Layout, Popconfirm, Select, Space } from "antd";

import GroupAddMember from "./AddMember";
import User from "../auth/User";
import type { IUser } from "../../api/Auth";
import {
  ASSIGNABLE_GROUP_ROLES,
  canManageMembers,
  fetchGroupMembers,
  removeGroupMember,
  updateGroupMemberRole,
  type AssignableGroupRole,
  type GroupMember,
} from "../../api/group-member";
import { useAppSelector } from "../../hooks";
import { studioList } from "../../reducers/current-user";

const { Content } = Layout;

const ROLE_COLOR: Record<string, string> = {
  owner: "success",
  manager: "processing",
  editor: "blue",
  reader: "default",
};

interface IWidgetGroupFile {
  groupId?: string;
}
const GroupMemberWidget = ({ groupId }: IWidgetGroupFile) => {
  const intl = useIntl(); //i18n
  const [memberCount, setMemberCount] = useState<number>();
  const studios = useAppSelector(studioList);

  // 我在这个团队里的角色来自 /v3/me/groups（成员列表分页，自己那行不一定在当前页）
  const myRole = studios?.find((item) => item.id === groupId)?.role;
  const canManage = canManageMembers(myRole);

  const roleLabel = (role?: string) =>
    intl.formatMessage({ id: `labels.group.role.${role ?? "reader"}` });

  const ref = useRef<ActionType | null>(null);
  const reload = () => ref.current?.reload();

  return (
    <Content>
      <ProList<GroupMember>
        rowKey="id"
        actionRef={ref}
        // groupId 放进 params：切换团队（团队空间或团队详情页）时 ProList 才会重新请求
        params={{ groupId }}
        headerTitle={
          intl.formatMessage({ id: "group.member" }) +
          "-" +
          (memberCount?.toString() ?? "")
        }
        toolBarRender={() => [
          canManage && groupId ? (
            <GroupAddMember key="add" groupId={groupId} onCreated={reload} />
          ) : undefined,
        ]}
        request={async (params = {}) => {
          if (!groupId) {
            return { total: 0, success: false, data: [] };
          }
          try {
            const res = await fetchGroupMembers(
              groupId,
              params.current ?? 1,
              params.pageSize ?? 20
            );
            setMemberCount(res.meta?.total);
            return {
              total: res.meta?.total ?? 0,
              success: true,
              data: res.data ?? [],
            };
          } catch {
            return { total: 0, success: false, data: [] };
          }
        }}
        pagination={{
          showQuickJumper: true,
          showSizeChanger: true,
        }}
        metas={{
          title: {
            render: (_dom, row) => row.user?.nickName,
          },
          avatar: {
            editable: false,
            render: (_dom, row) => (
              <User {...(row.user as IUser)} showName={false} />
            ),
          },
          subTitle: {
            render: (_dom, row) =>
              canManage && row.role !== "owner" ? (
                <Select<AssignableGroupRole>
                  size="small"
                  variant="borderless"
                  value={row.role as AssignableGroupRole}
                  options={ASSIGNABLE_GROUP_ROLES.map((role) => ({
                    value: role,
                    label: roleLabel(role),
                  }))}
                  onChange={(role) => {
                    if (groupId && row.id) {
                      updateGroupMemberRole(groupId, row.id, role).then(reload);
                    }
                  }}
                />
              ) : (
                <Tag color={ROLE_COLOR[row.role ?? "reader"]}>
                  {roleLabel(row.role)}
                </Tag>
              ),
          },
          actions: {
            render: (_dom, row) =>
              canManage && row.role !== "owner" ? (
                <Space>
                  <Popconfirm
                    title={intl.formatMessage({
                      id: "forms.message.member.remove",
                    })}
                    onConfirm={() => {
                      if (groupId && row.id) {
                        removeGroupMember(groupId, row.id).then(reload);
                      }
                    }}
                    okText={intl.formatMessage({ id: "buttons.ok" })}
                    cancelText={intl.formatMessage({ id: "buttons.cancel" })}
                  >
                    <Button size="small" type="link" danger>
                      {intl.formatMessage({ id: "buttons.remove" })}
                    </Button>
                  </Popconfirm>
                </Space>
              ) : null,
          },
        }}
      />
    </Content>
  );
};

export default GroupMemberWidget;
