import { Segmented } from "antd";
import { useState } from "react";
import { useIntl } from "react-intl";

interface IWidget {
  initMode?: string;
  onModeChange?: Function;
}
const ModeSwitchWidget = ({ initMode = "read", onModeChange }: IWidget) => {
  const intl = useIntl();
  const [mode, setMode] = useState<string>(initMode);
  return (
    <Segmented
      size="middle"
      style={{
        color: "rgb(134 134 134 / 90%)",
        backgroundColor: "rgb(129 129 129 / 17%)",
        display: "block",
      }}
      defaultValue={initMode}
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
        const newMode = value.toString();
        if (typeof onModeChange !== "undefined") {
          onModeChange(newMode);
        }
        setMode(newMode);
      }}
    />
  );
};

export default ModeSwitchWidget;
