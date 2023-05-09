import { Avatar, Space } from "antd";

export interface IUser {
  id: string;
  nickName: string;
  userName: string;
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
}: IUser) => {
  return (
    <Space>
      {showAvatar ? (
        <Avatar size="small">{nickName?.slice(0, 1)}</Avatar>
      ) : undefined}
      {showName ? nickName : undefined}
    </Space>
  );
};

export default UserWidget;
