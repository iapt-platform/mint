import { Avatar } from "antd";

export interface IUser {
  id: string;
  nickName: string;
  realName: string;
  avatar?: string;
}
const Widget = ({ nickName, realName, avatar }: IUser) => {
  return (
    <>
      <Avatar size="small">{nickName?.slice(0, 1)}</Avatar>
      {nickName}
    </>
  );
};

export default Widget;
