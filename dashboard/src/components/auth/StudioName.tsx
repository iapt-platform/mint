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
  showAvatar?: boolean;
  showName?: boolean;
  popOver?: React.ReactNode;
  onClick?: Function;
}
const StudioNameWidget = ({
  data,
  showAvatar = true,
  showName = true,
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
        {showAvatar ? (
          <Avatar size="small" src={data?.avatar}>
            {data?.nickName?.slice(0, 2)}
          </Avatar>
        ) : (
          <></>
        )}
        {showName ? data?.nickName : ""}
      </Space>
    </StudioCard>
  );
};

export default StudioNameWidget;
