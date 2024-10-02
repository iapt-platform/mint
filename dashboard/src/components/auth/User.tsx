import { Avatar, Popover, Space } from "antd";
import { getAvatarColor } from "./Studio";

export interface IUser {
  id: string;
  nickName: string;
  userName: string;
  avatar?: string;
}

interface IWidget {
  id?: string;
  nickName?: string;
  userName?: string;
  avatar?: string;
  showAvatar?: boolean;
  showName?: boolean;
  showUserName?: boolean;
}
const UserWidget = ({
  nickName,
  userName,
  avatar,
  showAvatar = true,
  showName = true,
  showUserName = false,
}: IWidget) => {
  return (
    <Popover
      content={
        <div>
          <div>
            <Avatar
              size="large"
              src={avatar}
              style={{ backgroundColor: getAvatarColor(nickName) }}
            >
              {nickName?.slice(0, 2)}
            </Avatar>
          </div>
          <div>{`${nickName}@${userName}`}</div>
        </div>
      }
    >
      <Space>
        {showAvatar ? (
          <Avatar
            size={"small"}
            src={avatar}
            style={{ backgroundColor: getAvatarColor(nickName) }}
          >
            {nickName?.slice(0, 2)}
          </Avatar>
        ) : undefined}
        {showName ? nickName : undefined}
        {showName && showUserName ? "@" : undefined}
        {showUserName ? userName : undefined}
      </Space>
    </Popover>
  );
};

export default UserWidget;
