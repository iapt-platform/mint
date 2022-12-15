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
  const navigate = useNavigate();
  const [userName, setUserName] = useState("");
  const [nickName, setNickName] = useState("");
  const user = useAppSelector(_currentUser);
  useEffect(() => {
    setUserName(user ? user.realName : "");
    setNickName(user ? user.nickName : "");
  }, [user]);

  const userCard = (
    <>
      <ProCard
        style={{ maxWidth: 500, minWidth: 300 }}
        actions={[
          <Tooltip title="设置">
            <SettingOutlined key="setting" />
          </Tooltip>,
          <Tooltip title="个人空间">
            <Link to={`/blog/${userName}/overview`}>
              <HomeOutlined key="home" />
            </Link>
          </Tooltip>,
          <Tooltip title="退出登录">
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
          <div>欢迎遨游法的海洋</div>
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
          >
            {nickName.slice(0, 1)}
          </Avatar>
        </Popover>
      </>
    );
  }
};

export default Widget;
