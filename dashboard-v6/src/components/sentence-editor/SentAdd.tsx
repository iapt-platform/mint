import { Button } from "antd";
import { useState } from "react";
import { PlusOutlined } from "@ant-design/icons";

import { useIntl } from "react-intl";
import type { IChannel, TChannelType } from "../../api/Channel";
import ChannelTableModal from "../channel/ChannelTableModal";

interface IWidget {
  disableChannels?: string[];
  type?: TChannelType;
  onSelect?: (channel: IChannel) => void;
}
const Widget = ({
  disableChannels,
  type = "translation",
  onSelect,
}: IWidget) => {
  const [channelPickerOpen, setChannelPickerOpen] = useState(false);
  const intl = useIntl();
  return (
    <ChannelTableModal
      disableChannels={disableChannels}
      channelType={type}
      trigger={
        <Button
          type="dashed"
          style={{ width: 300 }}
          icon={<PlusOutlined />}
          onClick={() => {
            setChannelPickerOpen(true);
          }}
        >
          {intl.formatMessage({ id: "buttons.new" })}
        </Button>
      }
      open={channelPickerOpen}
      onClose={() => setChannelPickerOpen(false)}
      onSelect={(channel: IChannel) => {
        setChannelPickerOpen(false);
        if (typeof onSelect !== "undefined") {
          onSelect(channel);
        }
      }}
    />
  );
};

export default Widget;
