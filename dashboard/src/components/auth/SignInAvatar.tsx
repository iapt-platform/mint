import { useIntl } from "react-intl";
import { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { Tooltip, Typography } from "antd";
import { Avatar } from "antd";
import { Popover } from "antd";
import { ProCard } from "@ant-design/pro-components";
import {
  UserOutlined,
  HomeOutlined,
  LogoutOutlined,
  SettingOutlined,
} from "@ant-design/icons";

import { useAppSelector } from "../../hooks";
import { currentUser as _currentUser } from "../../reducers/current-user";
import { TooltipPlacement } from "antd/lib/tooltip";
import SettingModal from "./setting/SettingModal";
import { AdminIcon } from "../../assets/icon";

const { Title, Paragraph } = Typography;

interface IWidget {
  placement?: TooltipPlacement;
  style?: React.CSSProperties;
}

const SignInAvatarWidget = ({ style, placement = "bottomRight" }: IWidget) => {
  const intl = useIntl();
  const navigate = useNavigate();
  const [userName, setUserName] = useState<string>();
  const [nickName, setNickName] = useState<string>();
  const user = useAppSelector(_currentUser);

  console.debug("user", user);
  useEffect(() => {
    setUserName(user?.realName);
    setNickName(user?.nickName);
  }, [user]);

  if (typeof user === "undefined") {
    return (
      <Link to="/anonymous/users/sign-in">
        {intl.formatMessage({
          id: "nut.users.sign-in-up.title",
        })}
      </Link>
    );
  } else {
    return (
      <>
        <Popover
          content={
            <ProCard
              style={{ maxWidth: 500, minWidth: 300 }}
              actions={[
                <Tooltip
                  title={intl.formatMessage({
                    id: "buttons.setting",
                  })}
                >
                  <SettingModal trigger={<SettingOutlined key="setting" />} />
                </Tooltip>,
                user.roles?.includes("root") ||
                user.roles?.includes("administrator") ? (
                  <Tooltip
                    title={intl.formatMessage({
                      id: "buttons.admin",
                    })}
                  >
                    <Link to={`/admin`}>
                      <AdminIcon />
                    </Link>
                  </Tooltip>
                ) : (
                  <></>
                ),
                <Tooltip
                  title={intl.formatMessage({
                    id: "columns.library.blog.label",
                  })}
                >
                  <Link to={`/blog/${userName}/overview`}>
                    <HomeOutlined key="home" />
                  </Link>
                </Tooltip>,
                <Tooltip
                  title={intl.formatMessage({
                    id: "buttons.sign-out",
                  })}
                >
                  <LogoutOutlined
                    key="logout"
                    onClick={() => {
                      sessionStorage.removeItem("token");
                      localStorage.removeItem("token");
                      navigate("/anonymous/users/sign-in");
                    }}
                  />
                </Tooltip>,
              ]}
            >
              <Paragraph>
                <Title level={3}>{nickName}</Title>
                <Paragraph style={{ textAlign: "right" }}>
                  {intl.formatMessage({
                    id: "buttons.welcome",
                  })}
                </Paragraph>
              </Paragraph>
            </ProCard>
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
              {nickName?.slice(0, 2)}
            </Avatar>
          </span>
        </Popover>
      </>
    );
  }
};

export default SignInAvatarWidget;
