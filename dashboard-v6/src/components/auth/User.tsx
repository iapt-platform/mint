import { Avatar, Popover, Space, Typography } from "antd";

import { getAvatarColor } from "./Studio";
const { Text } = Typography;

export interface IUser {
  id: string;
  nickName: string;
  userName: string;
  avatar?: string;
  roles?: string[];
}

interface IWidget {
  id?: string;
  nickName?: string;
  userName?: string;
  avatar?: string;
  showAvatar?: boolean;
  showName?: boolean;
  showUserName?: boolean;
  hidePopover?: boolean;
}
const UserWidget = ({
  nickName,
  userName,
  avatar,
  showAvatar = true,
  showName = true,
  showUserName = false,
  hidePopover = false,
}: IWidget) => {
  const inner = (
    <Space>
      {showAvatar ? (
        <Avatar
          size={"small"}
          src={avatar}
          style={
            avatar ? undefined : { backgroundColor: getAvatarColor(nickName) }
          }
        >
          {nickName?.slice(0, 2)}
        </Avatar>
      ) : undefined}
      {showName ? <Text>{nickName}</Text> : undefined}
      {showName && showUserName ? <Text>@</Text> : undefined}
      {showUserName ? <Text>{userName}</Text> : undefined}
    </Space>
  );
  return hidePopover ? (
    inner
  ) : (
    <Popover
      content={
        <div>
          <div>
            <Avatar
              size="large"
              src={avatar}
              style={
                avatar
                  ? undefined
                  : { backgroundColor: getAvatarColor(nickName) }
              }
            >
              {nickName?.slice(0, 2)}
            </Avatar>
          </div>
          <Text>{`${nickName}@${userName}`}</Text>
        </div>
      }
    >
      {inner}
    </Popover>
  );
};

export default UserWidget;
