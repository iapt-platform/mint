import { useNavigate, useParams } from "react-router-dom";
import { IChannel } from "../../../components/channel/Channel";

import ChannelTable from "../../../components/channel/ChannelTable";

const Widget = () => {
  const { studioname } = useParams();
  const navigate = useNavigate();
  return (
    <ChannelTable
      studioName={studioname}
      onSelect={(channel: IChannel) => {
        const url = `/studio/${studioname}/channel/${channel.id}`;
        navigate(url);
      }}
    />
  );
};

export default Widget;
