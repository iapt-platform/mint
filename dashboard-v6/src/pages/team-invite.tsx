import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import { useNavigate, useParams } from "react-router";
import { Alert, Button, Card, message, Space, Spin, Typography } from "antd";

import {
  acceptGroupInvite,
  fetchGroupInvite,
  type GroupInvite,
} from "../api/group-invite";
import { useAuth } from "../hooks/useAuth";
import { useAppDispatch } from "../hooks";
import { TO_HOME, TO_SIGN_IN } from "../reducers/current-user";
import { refreshMyGroups } from "../features/teamspace/refreshMyGroups";

/**
 * 打开团队邀请链接：「xxx 邀请你加入团队空间 yyy」，同意后以只读成员加入。
 *
 * 不登录也能看；点「登录后加入」去登录页，登录完经 `?url=` 回到这里再点同意。
 * 已经是成员的直接给「进入团队空间」。
 */
const Widget = () => {
  const intl = useIntl();
  const navigate = useNavigate();
  const dispatch = useAppDispatch();
  const { token } = useParams();
  const { isAuthenticated } = useAuth();
  const [invite, setInvite] = useState<GroupInvite>();
  const [state, setState] = useState<"loading" | "ready" | "invalid">(
    "loading"
  );
  const [joining, setJoining] = useState(false);

  useEffect(() => {
    if (!token) {
      setState("invalid");
      return;
    }
    setState("loading");
    fetchGroupInvite(token)
      .then((data) => {
        setInvite(data);
        setState(data ? "ready" : "invalid");
      })
      .catch(() => setState("invalid"));
  }, [token]);

  const roleLabel = (role?: string | null) =>
    intl.formatMessage({ id: `labels.group.role.${role ?? "reader"}` });

  const groupId = invite?.group?.id;
  const enterTeam = () => navigate(`/workspace/team/${groupId}`);

  const accept = async () => {
    if (!token) {
      return;
    }
    setJoining(true);
    try {
      // 失败（链接刚好过期、签发人被降级等）由 unwrap 统一弹提示
      const joined = await acceptGroupInvite(token);
      message.success(
        intl.formatMessage(
          { id: "message.group.invite.joined" },
          { name: joined?.name ?? invite?.group?.name }
        )
      );
      // 头像弹窗里的账户列表要多出这个团队
      await refreshMyGroups(dispatch);
      enterTeam();
    } finally {
      setJoining(false);
    }
  };

  const signIn = () =>
    navigate(`${TO_SIGN_IN}?url=${btoa(window.location.href)}`);

  const title = intl.formatMessage({ id: "labels.group.invite.title" });

  const body = () => {
    if (state === "loading") {
      return <Spin />;
    }
    if (state === "invalid" || !invite) {
      return (
        <Alert
          type="warning"
          showIcon
          title={intl.formatMessage({ id: "labels.group.invite.invalid" })}
        />
      );
    }
    return (
      <Space orientation="vertical" size="middle" style={{ width: "100%" }}>
        <Typography.Title level={4} style={{ margin: 0 }}>
          {intl.formatMessage(
            { id: "labels.group.invite.headline" },
            {
              inviter: invite.inviter?.nickName,
              name: invite.group?.name,
            }
          )}
        </Typography.Title>
        {invite.group?.description ? (
          <Typography.Paragraph type="secondary" style={{ margin: 0 }}>
            {invite.group.description}
          </Typography.Paragraph>
        ) : undefined}
        {invite.member_role ? (
          <>
            <Alert
              type="info"
              showIcon
              title={intl.formatMessage(
                { id: "labels.group.invite.member" },
                { role: roleLabel(invite.member_role) }
              )}
            />
            <Button type="primary" onClick={enterTeam}>
              {intl.formatMessage({ id: "buttons.group.invite.enter" })}
            </Button>
          </>
        ) : (
          <>
            <Typography.Text>
              {intl.formatMessage(
                { id: "labels.group.invite.role" },
                { role: roleLabel(invite.role) }
              )}
            </Typography.Text>
            <Space>
              {isAuthenticated ? (
                <Button type="primary" loading={joining} onClick={accept}>
                  {intl.formatMessage({ id: "buttons.group.invite.accept" })}
                </Button>
              ) : (
                <Button type="primary" onClick={signIn}>
                  {intl.formatMessage({ id: "buttons.group.invite.sign-in" })}
                </Button>
              )}
              <Button onClick={() => navigate(TO_HOME)}>
                {intl.formatMessage({ id: "buttons.group.invite.decline" })}
              </Button>
            </Space>
          </>
        )}
      </Space>
    );
  };

  return (
    <>
      <title>{title}</title>
      <Card title={title} style={{ width: 440, maxWidth: "100%" }}>
        {body()}
      </Card>
    </>
  );
};

export default Widget;
