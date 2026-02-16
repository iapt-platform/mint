import { Segmented } from "antd";
import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import { useAppSelector } from "../../hooks";
import { rightPanel } from "../../reducers/right-panel";
import { TPanelName } from "./RightPanel";

interface IWidget {
  initMode?: string;
  onModeChange?: Function;
}
const RightToolsSwitchWidget = ({
  initMode = "close",
  onModeChange,
}: IWidget) => {
  const intl = useIntl();
  const [mode, setMode] = useState<string>(initMode);
  const _openPanel = useAppSelector(rightPanel);

  useEffect(() => {
    if (typeof _openPanel !== "undefined") {
      if (typeof onModeChange !== "undefined") {
        onModeChange(_openPanel);
      }
      setMode(_openPanel);
    }
  }, [_openPanel]);

  return (
    <Segmented
      size="middle"
      style={{
        color: "rgb(134 134 134 / 90%)",
        backgroundColor: "rgb(129 129 129 / 17%)",
        display: "block",
      }}
      options={[
        {
          label: intl.formatMessage({ id: "columns.library.dict.title" }),
          value: "dict",
        },
        {
          label: intl.formatMessage({ id: "columns.studio.channel.title" }),
          value: "channel",
        },
        {
          label: intl.formatMessage({ id: "buttons.close" }),
          value: "close",
        },
      ]}
      value={mode}
      onChange={(value) => {
        const newMode: TPanelName = value.toString() as TPanelName;
        if (typeof onModeChange !== "undefined") {
          onModeChange(newMode);
        }
        setMode(newMode);
      }}
    />
  );
};

export default RightToolsSwitchWidget;
