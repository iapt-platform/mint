import { Avatar, Space } from "antd";

import StudioCard from "./StudioCard";

export interface IStudio {
  id: string;
  nickName: string;
  studioName: string;
  avatar?: string;
}
interface IWidghtStudio {
  data?: IStudio;
  showAvatar?: boolean;
  showName?: boolean;
  onClick?: Function;
}
const Widget = ({
  data,
  showAvatar = true,
  showName = true,
  onClick,
}: IWidghtStudio) => {
  // TODO
  const avatar = <Avatar size="small">{data?.nickName.slice(0, 1)}</Avatar>;
  return (
    <StudioCard studio={data}>
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

export default Widget;
