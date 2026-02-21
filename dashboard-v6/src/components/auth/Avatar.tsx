import { useIntl } from "react-intl";
import { Link, useNavigate } from "react-router";
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
import {
  currentUser as _currentUser,
  type IUser,
} from "../../reducers/current-user";
import type { TooltipPlacement } from "antd/lib/tooltip";
import SettingModal from "../setting/SettingModal";
import LoginButton from "./LoginButton";

const { Title } = Typography;

interface IUserCard {
  user?: IUser;
}
const UserCard = ({ user }: IUserCard) => {
  const intl = useIntl();
  const navigate = useNavigate();
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
          <Link to={`/blog/${user?.realName}/overview`}>
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
        <Title level={4}>{user?.nickName}</Title>
        <div style={{ textAlign: "right" }}>
          {intl.formatMessage({
            id: "buttons.welcome",
          })}
        </div>
      </div>
    </ProCard>
  );
};

interface IWidget {
  placement?: TooltipPlacement;
  style?: React.CSSProperties;
}
const AvatarWidget = ({ style, placement = "bottomRight" }: IWidget) => {
  const user = useAppSelector(_currentUser);

  return (
    <>
      <Popover
        content={user ? <UserCard user={user} /> : <LoginButton />}
        placement={placement}
      >
        <span style={style}>
          <Avatar
            style={{ backgroundColor: user ? "#87d068" : "gray" }}
            icon={user?.avatar ? undefined : <UserOutlined />}
            src={user?.avatar}
            size="small"
          >
            {user ? user?.nickName?.slice(0, 1) : undefined}
          </Avatar>
        </span>
      </Popover>
    </>
  );
};

export default AvatarWidget;
