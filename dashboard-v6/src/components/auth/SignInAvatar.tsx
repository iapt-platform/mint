import { useIntl } from "react-intl";
import { useState } from "react";
import { useNavigate } from "react-router";
import { Divider, Menu, Typography } from "antd";
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

const { Title, Paragraph, Text } = Typography;

/** 账户列表里个人空间与各团队空间的菜单 key */
const PERSONAL_KEY = "space:personal";
const TEAMSPACE_PREFIX = "space:team:";

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

  console.debug("user", user);

  const canManage =
    user?.roles?.includes("root") || user?.roles?.includes("administrator");

  if (typeof user === "undefined") {
    return <LoginButton />;
  } else {
    const welcome = (
      <Paragraph>
        <Title level={3} style={{ fontSize: 22 }}>
          {user.nickName}
        </Title>
        <Text type="secondary">账户名 {user.realName}</Text>
        <Paragraph style={{ textAlign: "right", paddingTop: 30 }}>
          {intl.formatMessage({
            id: "buttons.welcome",
          })}
        </Paragraph>
      </Paragraph>
    );

    const current = <CheckOutlined style={{ marginLeft: 8 }} />;
    let userList = [
      {
        key: PERSONAL_KEY,
        label: (
          <>
            <User {...user} />
            {!teamspace && current}
          </>
        ),
      },
    ];
    const studioList = studios?.map((item) => {
      return {
        key: TEAMSPACE_PREFIX + item.id,
        label: (
          <>
            <Studio data={item} />
            {teamspace?.id === item.id && current}
          </>
        ),
      };
    });
    if (studioList) {
      userList = [...userList, ...studioList];
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
                        id: "buttons.teamspace.switch",
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
          <span style={style}>
            <Avatar
              style={{ backgroundColor: "#87d068" }}
              icon={<UserOutlined />}
              src={user?.avatar}
              size="small"
            >
              {user.nickName?.slice(0, 2)}
            </Avatar>
            {!hideName && user.nickName}
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
