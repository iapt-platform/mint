import { useIntl } from "react-intl";
import { useRef, useState } from "react";
import { type ActionType, ProList } from "@ant-design/pro-components";
import { Space, Tag, Button, Layout, Popconfirm } from "antd";

import {
  fetchGroupShares,
  removeGroupShare,
  type GroupShare,
} from "../../api/group";
import { canManageMembers } from "../../api/group-member";
import { useAppSelector } from "../../hooks";
import { currentUser, studioList } from "../../reducers/current-user";

const { Content } = Layout;

/** 资源类型 → 界面上的名字（沿用各模块的栏目名） */
const TYPE_LABEL: Record<string, string> = {
  channel: "columns.studio.channel.title",
  article: "columns.studio.article.title",
  collection: "columns.studio.anthology.title",
};

interface IWidget {
  groupId?: string;
}
/**
 * 团队文档：共享给这个团队的资源（v3 /groups/{group}/shares）。
 * 团队的 owner / manager，或资源的拥有者，可以把资源移出团队。
 */
const GroupFileWidget = ({ groupId }: IWidget) => {
  const intl = useIntl(); //i18n
  const [resCount, setResCount] = useState(0);
  const user = useAppSelector(currentUser);
  const studios = useAppSelector(studioList);
  // 管理成员与管理团队文档是同一组角色（owner / manager）
  const canManage = canManageMembers(
    studios?.find((item) => item.id === groupId)?.role
  );

  const ref = useRef<ActionType | null>(null);
  return (
    <Content>
      <ProList<GroupShare>
        rowKey="id"
        actionRef={ref}
        // groupId 放进 params：切换团队（团队空间或团队详情页）时 ProList 才会重新请求
        params={{ groupId }}
        headerTitle={
          intl.formatMessage({ id: "group.files" }) + "-" + resCount.toString()
        }
        metas={{
          title: {
            render: (_dom, row) =>
              row.resource?.title ??
              intl.formatMessage({ id: "labels.group.share.missing" }),
          },
          description: {
            render: (_dom, row) => row.resource?.owner?.nickName,
          },
          subTitle: {
            render: (_dom, row) => {
              const type = row.resource?.type ?? "";
              return (
                <Space size={0}>
                  <Tag>
                    {TYPE_LABEL[type]
                      ? intl.formatMessage({ id: TYPE_LABEL[type] })
                      : type}
                  </Tag>
                  <Tag color={row.role === "editor" ? "blue" : "default"}>
                    {intl.formatMessage({
                      id: `labels.group.share.${row.role ?? "reader"}`,
                    })}
                  </Tag>
                </Space>
              );
            },
          },
          actions: {
            render: (_dom, row) =>
              canManage ||
              (user?.id && row.resource?.owner_id === user.id) ? (
                <Popconfirm
                  title={intl.formatMessage({
                    id: "forms.message.res.remove",
                  })}
                  onConfirm={() => {
                    if (groupId && row.id) {
                      removeGroupShare(groupId, row.id).then(() =>
                        ref.current?.reload()
                      );
                    }
                  }}
                  okText={intl.formatMessage({ id: "buttons.ok" })}
                  cancelText={intl.formatMessage({ id: "buttons.cancel" })}
                >
                  <Button size="small" type="link" danger>
                    {intl.formatMessage({ id: "buttons.remove" })}
                  </Button>
                </Popconfirm>
              ) : null,
          },
        }}
        request={async (params = {}) => {
          if (!groupId) {
            return { total: 0, success: false, data: [] };
          }
          try {
            const res = await fetchGroupShares(
              groupId,
              params.current ?? 1,
              params.pageSize ?? 20
            );
            setResCount(res.meta?.total ?? 0);
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
      />
    </Content>
  );
};

export default GroupFileWidget;
