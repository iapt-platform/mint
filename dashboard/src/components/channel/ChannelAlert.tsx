import { useIntl } from "react-intl";

import { Alert, Button } from "antd";

import ChannelPicker from "./ChannelPicker";
import { IChannel } from "./Channel";
import store from "../../store";
import { openPanel } from "../../reducers/right-panel";

interface IWidget {
  channels?: string | null;
  onChannelChange?: Function;
}
const ChannelAlertWidget = ({ channels, onChannelChange }: IWidget) => {
  const intl = useIntl();

  // 获取浏览器宽度
  const browserWidth = window.innerWidth;
  let button = <></>;
  if (browserWidth < 580) {
    button = (
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
    );
  } else {
    button = (
      <Button
        type="primary"
        onClick={() => {
          store.dispatch(openPanel("channel"));
        }}
      >
        {intl.formatMessage({
          id: "buttons.select.channel",
        })}
      </Button>
    );
  }

  return channels ? (
    <></>
  ) : (
    <Alert
      message={intl.formatMessage({
        id: "message.channel.empty.alert",
      })}
      type="warning"
      closable
      action={button}
    />
  );
};

export default ChannelAlertWidget;
