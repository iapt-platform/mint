import { Avatar, Space } from "antd";

import StudioCard from "./StudioCard";

const avatarColor = ["indianred", "blueviolet", "#87d068", "#108ee9"];
export interface IStudio {
  id: string;
  nickName?: string;
  studioName?: string;
  realName?: string;
  avatar?: string;
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
  let colorIndex = 0;
  if (data?.nickName) {
    let char = 0;
    if (data.nickName.length > 1) {
      char = data.nickName.length - 1;
    }
    colorIndex = data.nickName.charCodeAt(char) % avatarColor.length;
  }

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
            style={{ backgroundColor: avatarColor[colorIndex] }}
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
