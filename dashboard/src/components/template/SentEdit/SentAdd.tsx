import { Button } from "antd";
import { useState } from "react";
import { PlusOutlined } from "@ant-design/icons";

import { IChannel } from "../../channel/Channel";
import ChannelTableModal from "../../channel/ChannelTableModal";
import { TChannelType } from "../../api/Channel";

interface IWidget {
  disableChannels?: string[];
  type?: TChannelType;
  onSelect?: Function;
}
const Widget = ({
  disableChannels,
  type = "translation",
  onSelect,
}: IWidget) => {
  const [channelPickerOpen, setChannelPickerOpen] = useState(false);

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
          Add
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
