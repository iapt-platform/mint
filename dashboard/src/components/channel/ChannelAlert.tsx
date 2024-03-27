import { useIntl } from "react-intl";

import { Alert, Button } from "antd";

import ChannelPicker from "./ChannelPicker";
import { IChannel } from "./Channel";

interface IWidget {
  channels?: string | null;
  onChannelChange?: Function;
}
const ChannelAlertWidget = ({ channels, onChannelChange }: IWidget) => {
  const intl = useIntl();

  return channels ? (
    <></>
  ) : (
    <Alert
      message={intl.formatMessage({
        id: "message.channel.empty.alert",
      })}
      type="warning"
      closable
      action={
        <ChannelPicker
          trigger={
            <Button type="primary">
              {intl.formatMessage({
                id: "buttons.select.channel",
              })}
            </Button>
          }
          defaultOwner="my"
          onSelect={(channels: IChannel[]) => {
            if (typeof onChannelChange !== "undefined") {
              onChannelChange(channels);
            }
          }}
        />
      }
    />
  );
};

export default ChannelAlertWidget;
