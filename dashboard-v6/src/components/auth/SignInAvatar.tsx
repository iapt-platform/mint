import { useIntl } from "react-intl";
import { useState } from "react";
import { useNavigate } from "react-router";
import { Divider, Menu, Tag, Typography } from "antd";
import { Avatar } from "antd";
import { Popover } from "antd";

import {
  UserOutlined,
  HomeOutlined,
  LogoutOutlined,
  SettingOutlined,
  CheckOutlined,
  SwapOutlined,
} from "@ant-design/icons";

import { useAppDispatch, useAppSelector } from "../../hooks";
import {
  currentUser as _currentUser,
  currentStudio,
  setCurrStudio,
  studioList,
  studioSignOut,
  TO_SIGN_IN,
} from "../../reducers/current-user";
import type { TooltipPlacement } from "antd/lib/tooltip";
import SettingModal from "../setting/SettingModal";
import { AdminIcon } from "../../assets/icon";
import User from "./User";
import { fullUrl } from "../../utils";
import Studio from "./Studio";
import LoginButton from "./LoginButton";
import "./SignInAvatar.css";
import { getAvatarColor } from "./utils";

const { Title, Paragraph, Text } = Typography;

/** 账户列表里个人空间与各团队空间的菜单 key */
const PERSONAL_KEY = "space:personal";
const TEAMSPACE_PREFIX = "space:team:";

/** 团队角色标签的颜色（antd 预设色：浅色底、同色边框与文字） */
const ROLE_COLOR: Record<string, string> = {
  owner: "gold",
  manager: "blue",
  editor: "green",
  reader: "default",
};

interface IWidget {
  placement?: TooltipPlacement;
  style?: React.CSSProperties;
  hideName?: boolean;
}

const SignInAvatar = ({
  style,
  placement = "bottomRight",
  hideName = false,
}: IWidget) => {
  const intl = useIntl();
  const navigate = useNavigate();
  const dispatch = useAppDispatch();
  const [settingOpen, setSettingOpen] = useState(false);

  const user = useAppSelector(_currentUser);
  const studios = useAppSelector(studioList);
  const teamspace = useAppSelector(currentStudio);

  const canManage =
    user?.roles?.includes("root") || user?.roles?.includes("administrator");

  if (typeof user === "undefined") {
    return <LoginButton />;
  } else {
    /** 团队角色的显示名；没有对应文案的（如旧数据里的 member）原样显示 */
    const roleLabel = (role?: string) =>
      role
        ? intl.formatMessage({
            id: `labels.group.role.${role}`,
            defaultMessage: role,
          })
        : undefined;

    // 当前账号：团队空间里是团队，否则是登录用户本人
    const activeName = teamspace ? teamspace.nickName : user.nickName;
    const activeAvatar = teamspace ? teamspace.avatar : user.avatar;

    const welcome = (
      <Paragraph>
        <Title
          level={3}
          style={{ fontSize: 22 }}
          ellipsis={{ tooltip: activeName }}
        >
          {activeName}
        </Title>
        {teamspace ? (
          teamspace.role && (
            <Tag
              data-testid="account-role"
              color={ROLE_COLOR[teamspace.role] ?? "default"}
            >
              {roleLabel(teamspace.role)}
            </Tag>
          )
        ) : (
          <Text
            type="secondary"
            style={{ display: "block" }}
            ellipsis={{ tooltip: user.realName }}
          >
            账户名 {user.realName}
          </Text>
        )}
        <Paragraph style={{ textAlign: "right", paddingTop: 30 }}>
          {intl.formatMessage({
            id: "buttons.welcome",
          })}
        </Paragraph>
      </Paragraph>
    );

    const current = <CheckOutlined style={{ marginLeft: 8 }} />;
    type AccountItem =
      | { key: string; label: React.ReactNode }
      | { type: "divider"; key: string };
    let userList: AccountItem[] = [
      {
        key: PERSONAL_KEY,
        label: (
          <span className="account-item" data-testid="account-personal">
            <span className="account-item__name">
              <User {...user} />
            </span>
            {!teamspace && current}
          </span>
        ),
      },
    ];
    const studioList = studios?.map((item) => {
      return {
        key: TEAMSPACE_PREFIX + item.id,
        label: (
          <span className="account-item">
            <span className="account-item__name">
              <Studio data={item} />
            </span>
            {item.role && (
              <Tag style={{ marginInlineStart: 8 }}>{roleLabel(item.role)}</Tag>
            )}
            {teamspace?.id === item.id && current}
          </span>
        ),
      };
    });
    if (studioList?.length) {
      userList = [
        ...userList,
        { type: "divider", key: "space:divider" },
        ...studioList,
      ];
    }
    return (
      <>
        <Popover
          content={
            <div style={{ width: 350 }}>
              <>{welcome}</>
              <Divider></Divider>
              <div style={{ maxHeight: 500, overflowY: "auto" }}>
                <Menu
                  style={{ width: "100%" }}
                  mode={"inline"}
                  selectable={false}
                  items={[
                    {
                      key: "account",
                      label: intl.formatMessage({
                        id: "buttons.account.switch",
                      }),
                      icon: <SwapOutlined />,
                      children: userList,
                    },
                    {
                      key: "setting",
                      label: "设置",
                      icon: <SettingOutlined />,
                    },
                    {
                      key: "admin",
                      label: intl.formatMessage({
                        id: "buttons.admin",
                      }),
                      icon: <AdminIcon />,
                      disabled: !canManage,
                    },
                    {
                      key: "blog",
                      label: intl.formatMessage({
                        id: "columns.library.blog.label",
                      }),
                      icon: <HomeOutlined key="home" />,
                    },
                    {
                      key: "logout",
                      label: intl.formatMessage({
                        id: "buttons.sign-out",
                      }),
                      icon: <LogoutOutlined />,
                    },
                  ].filter((value) => !value.disabled)}
                  onClick={(info) => {
                    if (info.key === PERSONAL_KEY) {
                      dispatch(studioSignOut());
                      return;
                    }
                    if (info.key.startsWith(TEAMSPACE_PREFIX)) {
                      const id = info.key.slice(TEAMSPACE_PREFIX.length);
                      const studio = studios?.find((item) => item.id === id);
                      if (studio) {
                        dispatch(setCurrStudio(studio));
                      }
                      return;
                    }
                    switch (info.key) {
                      case "setting":
                        setSettingOpen(true);
                        break;
                      case "admin":
                        window.open(fullUrl(`/admin`), "_blank");
                        break;
                      case "blog":
                        window.open(
                          fullUrl(`/blog/${user.realName}/overview`),
                          "_blank"
                        );
                        break;
                      case "logout":
                        sessionStorage.removeItem("token");
                        localStorage.removeItem("token");
                        dispatch(studioSignOut());
                        navigate(TO_SIGN_IN);
                        break;
                    }
                  }}
                />
              </div>
            </div>
          }
          placement={placement}
        >
          <span
            style={{
              display: "inline-flex",
              alignItems: "center",
              flex: "0 1 auto",
              minWidth: 0,
              ...style,
            }}
            data-testid="active-account"
          >
            <Avatar
              style={{
                flex: "none",
                backgroundColor: teamspace
                  ? getAvatarColor(activeName)
                  : "#87d068",
              }}
              icon={teamspace ? undefined : <UserOutlined />}
              src={activeAvatar}
              size="small"
            >
              {activeName?.slice(0, 2)}
            </Avatar>
            {!hideName && (
              <span
                style={{
                  marginInlineStart: 8,
                  minWidth: 0,
                  overflow: "hidden",
                  textOverflow: "ellipsis",
                  whiteSpace: "nowrap",
                }}
                title={activeName}
              >
                {activeName}
              </span>
            )}
          </span>
        </Popover>
        <SettingModal
          open={settingOpen}
          onClose={() => setSettingOpen(false)}
        />
      </>
    );
  }
};

export default SignInAvatar;
