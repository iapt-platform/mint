import { Avatar, Space } from "antd";

import StudioCard from "./StudioCard";
import type { IStudio } from "../../api/Auth";
import { getAvatarColor } from "./utils";

interface IWidget {
  data?: IStudio;
  hideAvatar?: boolean;
  hideName?: boolean;
  popOver?: React.ReactNode;
  onClick?: (studioName?: string) => void;
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
