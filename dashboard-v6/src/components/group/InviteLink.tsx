import { useIntl } from "react-intl";
import { useState } from "react";
import { Button, Input, message, Modal, Space, Typography } from "antd";
import { CopyOutlined, LinkOutlined } from "@ant-design/icons";

import { createGroupInviteLink, groupInviteUrl } from "../../api/group-invite";

interface IWidget {
  groupId: string;
}

/**
 * 「获取邀请链接」按钮：签一张链接，弹窗里给出地址与复制按钮。
 * 每点一次签一张新的（旧的仍有效到过期），链接不落库、没有列表。
 */
const InviteLinkWidget = ({ groupId }: IWidget) => {
  const intl = useIntl();
  const [loading, setLoading] = useState(false);
  const [link, setLink] = useState<{ url: string; expiresAt: string }>();

  const open = async () => {
    setLoading(true);
    try {
      // 失败（不是管理者等）由 unwrap 统一弹提示
      const data = await createGroupInviteLink(groupId);
      if (data?.token) {
        setLink({
          url: groupInviteUrl(data.token),
          expiresAt: data.expires_at ?? "",
        });
      }
    } finally {
      setLoading(false);
    }
  };

  const copy = () => {
    if (!link) {
      return;
    }
    navigator.clipboard.writeText(link.url).then(() => {
      message.success(intl.formatMessage({ id: "message.copy.success" }));
    });
  };

  return (
    <>
      <Button icon={<LinkOutlined />} loading={loading} onClick={open}>
        {intl.formatMessage({ id: "buttons.group.invite.link" })}
      </Button>
      <Modal
        open={!!link}
        title={intl.formatMessage({ id: "buttons.group.invite.link" })}
        onCancel={() => setLink(undefined)}
        footer={null}
        destroyOnHidden
      >
        <Space orientation="vertical" style={{ width: "100%" }}>
          <Space.Compact style={{ width: "100%" }}>
            <Input readOnly value={link?.url} onFocus={(e) => e.target.select()} />
            <Button type="primary" icon={<CopyOutlined />} onClick={copy}>
              {intl.formatMessage({ id: "buttons.copy" })}
            </Button>
          </Space.Compact>
          <Typography.Text type="secondary">
            {intl.formatMessage(
              { id: "labels.group.invite.link.hint" },
              {
                expires: link?.expiresAt
                  ? new Date(link.expiresAt).toLocaleString()
                  : "",
              }
            )}
          </Typography.Text>
        </Space>
      </Modal>
    </>
  );
};

export default InviteLinkWidget;
