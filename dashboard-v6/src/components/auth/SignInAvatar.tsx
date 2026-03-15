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
} from "@ant-design/icons";

import { useAppSelector } from "../../hooks";
import {
  currentUser as _currentUser,
  studioList,
} from "../../reducers/current-user";
import type { TooltipPlacement } from "antd/lib/tooltip";
import SettingModal from "../setting/SettingModal";
import { AdminIcon } from "../../assets/icon";
import User from "./User";
import { fullUrl } from "../../utils";
import Studio from "./Studio";
import LoginButton from "./LoginButton";

const { Title, Paragraph, Text } = Typography;

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
  const [settingOpen, setSettingOpen] = useState(false);

  const user = useAppSelector(_currentUser);
  const studios = useAppSelector(studioList);

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

    let userList = [
      {
        key: user.realName,
        label: <User {...user} />,
      },
    ];
    const studioList = studios?.map((item) => {
      return {
        key: item.realName ?? "",
        label: <Studio data={item} />,
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
                      label: "选择账户",
                      icon: <UserOutlined />,
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
                        navigate("/anonymous/users/sign-in");
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
