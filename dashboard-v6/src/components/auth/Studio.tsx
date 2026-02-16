import { Avatar, Space } from "antd";

import StudioCard from "./StudioCard";

export const getAvatarColor = (name?: string) => {
  const avatarColor = ["indianred", "blueviolet", "#87d068", "#108ee9"];
  if (!name) {
    return undefined;
  }
  let char = 0;
  if (name.length > 1) {
    char = name.length - 1;
  }
  const colorIndex = name.charCodeAt(char) % avatarColor.length;
  return avatarColor[colorIndex];
};
export interface IStudio {
  id: string;
  nickName?: string;
  studioName?: string;
  realName?: string;
  avatar?: string;
  roles?: string[];
}
interface IWidget {
  data?: IStudio;
  hideAvatar?: boolean;
  hideName?: boolean;
  popOver?: React.ReactNode;
  onClick?: Function;
}
const StudioWidget = ({
  data,
  hideAvatar = false,
  hideName = false,
  popOver,
  onClick,
}: IWidget) => {
  return (
    <StudioCard popOver={popOver} studio={data}>
      <Space
        onClick={() => {
          if (typeof onClick !== "undefined") {
            onClick(data?.studioName);
          }
        }}
      >
        {hideAvatar ? (
          <></>
        ) : (
          <Avatar
            size="small"
            src={data?.avatar}
            style={{ backgroundColor: getAvatarColor(data?.nickName) }}
          >
            {data?.nickName?.slice(0, 2)}
          </Avatar>
        )}
        {hideName ? "" : data?.nickName}
      </Space>
    </StudioCard>
  );
};

export default StudioWidget;
