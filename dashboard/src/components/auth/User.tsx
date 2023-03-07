import { Avatar, Space } from "antd";

export interface IUser {
  id: string;
  nickName: string;
  userName: string;
  avatar?: string;
}
const Widget = ({ nickName, userName, avatar }: IUser) => {
  return (
    <Space>
      <Avatar size="small">{nickName?.slice(0, 1)}</Avatar>
      {nickName}
    </Space>
  );
};

export default Widget;
