import { Avatar, Space } from "antd";

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
}
const UserWidget = ({
  nickName,
  userName,
  avatar,
  showAvatar = true,
  showName = true,
}: IWidget) => {
  return (
    <Space>
      {showAvatar ? (
        <Avatar size="small" src={avatar}>
          {nickName?.slice(0, 2)}
        </Avatar>
      ) : undefined}
      {showName ? nickName : undefined}
    </Space>
  );
};

export default UserWidget;
