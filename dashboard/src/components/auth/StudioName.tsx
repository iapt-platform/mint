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
  const avatar = <Avatar size="small">{data?.nickName?.slice(0, 1)}</Avatar>;
  return (
    <StudioCard popOver={popOver} studio={data}>
      <Space
        onClick={() => {
          if (typeof onClick !== "undefined") {
            onClick(data?.studioName);
          }
        }}
      >
        {showAvatar ? avatar : ""}
        {showName ? data?.nickName : ""}
      </Space>
    </StudioCard>
  );
};

export default StudioNameWidget;
