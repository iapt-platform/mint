import { Avatar, Space } from "antd";

import StudioCard from "./StudioCard";

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
  console.debug("studio", data);
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
          <Avatar size="small" src={data?.avatar}>
            {data?.nickName?.slice(0, 2)}
          </Avatar>
        )}
        {hideName ? "" : data?.nickName}
      </Space>
    </StudioCard>
  );
};

export default StudioWidget;
