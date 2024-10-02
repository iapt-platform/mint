import { Segmented } from "antd";
import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import { IChannel } from "../channel/Channel";
import ChannelPicker from "../channel/ChannelPicker";

interface IWidget {
  currMode?: string;
  channel: string | null;
  onModeChange?: Function;
  onChannelChange?: Function;
}
const ModeSwitchWidget = ({
  currMode = "read",
  onModeChange,
  channel,
  onChannelChange,
}: IWidget) => {
  const intl = useIntl();
  const [mode, setMode] = useState<string>(currMode);
  const [newMode, setNewMode] = useState<string>();
  const [channelPickerOpen, setChannelPickerOpen] = useState(false);
  useEffect(() => {
    setMode(currMode);
  }, [currMode]);
  return (
    <>
      <Segmented
        size="middle"
        style={{
          color: "rgb(134 134 134 / 90%)",
          backgroundColor: "rgb(129 129 129 / 17%)",
          display: "block",
        }}
        defaultValue={currMode}
        options={[
          {
            label: intl.formatMessage({ id: "buttons.read" }),
            value: "read",
          },
          {
            label: intl.formatMessage({ id: "buttons.translate" }),
            value: "edit",
          },
          {
            label: intl.formatMessage({ id: "buttons.wbw" }),
            value: "wbw",
          },
        ]}
        value={mode}
        onChange={(value) => {
          const _mode = value.toString();

          if (_mode !== "read" && channel === null) {
            setChannelPickerOpen(true);
            setNewMode(_mode);
          } else {
            if (typeof onModeChange !== "undefined") {
              onModeChange(_mode);
            }
            setMode(_mode);
          }
        }}
      />
      <ChannelPicker
        open={channelPickerOpen}
        defaultOwner="my"
        onClose={() => setChannelPickerOpen(false)}
        onSelect={(channels: IChannel[]) => {
          if (newMode) {
            setMode(newMode);
          }
          if (typeof onChannelChange !== "undefined") {
            onChannelChange(channels, newMode);
          }
        }}
      />
    </>
  );
};

export default ModeSwitchWidget;
