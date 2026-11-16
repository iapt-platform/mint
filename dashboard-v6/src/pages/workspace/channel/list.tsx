import { useNavigate } from "react-router";
import { useIntl } from "react-intl";

import ChannelTable from "../../../components/channel/ChannelTable";
import type { IChannel } from "../../../api/channel";
import { useActiveStudio } from "../../../hooks/useActiveStudio";

const Widget = () => {
  const { studioName } = useActiveStudio();
  const navigate = useNavigate();
  const intl = useIntl();
  console.debug("channel list", studioName);
  return (
    <>
      <title>
        {intl.formatMessage({ id: "columns.studio.channel.title" })}
      </title>
      <ChannelTable
      studioName={studioName}
      onSelect={(channel: IChannel) => {
        const url = `/workspace/channel/${channel.id}`;
        navigate(url);
      }}
      />
    </>
  );
};

export default Widget;
