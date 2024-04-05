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

const { Title } = Typography;

interface IWidget {
  placement?: TooltipPlacement;
  style?: React.CSSProperties;
}
const AvatarWidget = ({ style, placement = "bottomRight" }: IWidget) => {
  const intl = useIntl();
  const navigate = useNavigate();
  const [userName, setUserName] = useState<string>();
  const [nickName, setNickName] = useState<string>();
  const user = useAppSelector(_currentUser);
  useEffect(() => {
    setUserName(user?.realName);
    setNickName(user?.nickName);
  }, [user]);

  const UserCard = () => {
    return (
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
        <div>
          <Title level={4}>{nickName}</Title>
          <div style={{ textAlign: "right" }}>
            {intl.formatMessage({
              id: "buttons.welcome",
            })}
          </div>
        </div>
      </ProCard>
    );
  };
  const Login = () => (
    <Link to="/anonymous/users/sign-in">
      {intl.formatMessage({
        id: "nut.users.sign-in-up.title",
      })}
    </Link>
  );
  return (
    <>
      <Popover content={user ? <UserCard /> : <Login />} placement={placement}>
        <span style={style}>
          <Avatar
            style={{ backgroundColor: user ? "#87d068" : "gray" }}
            icon={user?.avatar ? undefined : <UserOutlined />}
            src={user?.avatar}
            size="small"
          >
            {user ? nickName?.slice(0, 1) : undefined}
          </Avatar>
        </span>
      </Popover>
    </>
  );
};

export default AvatarWidget;
