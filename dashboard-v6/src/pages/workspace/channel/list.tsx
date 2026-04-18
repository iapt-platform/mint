import { useNavigate } from "react-router";

import ChannelTable from "../../../components/channel/ChannelTable";
import type { IChannel } from "../../../api/channel";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";

const Widget = () => {
  const user = useAppSelector(currentUser);
  const studioName = user?.realName;
  const navigate = useNavigate();
  console.debug("channel list", studioName);
  return (
    <ChannelTable
      studioName={studioName}
      onSelect={(channel: IChannel) => {
        const url = `/workspace/channel/${channel.id}`;
        navigate(url);
      }}
    />
  );
};

export default Widget;
