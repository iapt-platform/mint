export interface IUser {
  id?: string;
  nickName?: string;
  realName?: string;
  onClick?: Function;
}
const UserNameWidget = ({ id, nickName, realName, onClick }: IUser) => {
  return (
    <span
      onClick={(e) => {
        if (typeof onClick !== "undefined") {
          onClick(e);
        }
      }}
    >
      {nickName}
    </span>
  );
};

export default UserNameWidget;
