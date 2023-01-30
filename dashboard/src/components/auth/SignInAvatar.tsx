import { useIntl } from "react-intl";
import { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { Tooltip } from "antd";
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

const Widget = () => {
  // TODO
  const intl = useIntl();
  const navigate = useNavigate();
  const [userName, setUserName] = useState<string>();
  const [nickName, setNickName] = useState<string>();
  const user = useAppSelector(_currentUser);
  useEffect(() => {
    setUserName(user?.realName);
    setNickName(user?.nickName);
  }, [user]);

  const userCard = (
    <>
      <ProCard
        style={{ maxWidth: 500, minWidth: 300 }}
        actions={[
          <Tooltip
            title={intl.formatMessage({
              id: "buttons.setting",
            })}
          >
            <SettingOutlined key="setting" />
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
          <h2>{nickName}</h2>
          <div style={{ textAlign: "right" }}>
            {intl.formatMessage({
              id: "buttons.welcome",
            })}
          </div>
        </div>
      </ProCard>
    </>
  );

  if (typeof user === "undefined") {
    return <Link to="/anonymous/users/sign-in">登录</Link>;
  } else {
    return (
      <>
        <Popover content={userCard} placement="bottomRight">
          <Avatar
            style={{ backgroundColor: "#87d068" }}
            icon={<UserOutlined />}
            size="small"
          >
            {nickName?.slice(0, 1)}
          </Avatar>
        </Popover>
      </>
    );
  }
};

export default Widget;
