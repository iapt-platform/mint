import { Segmented } from "antd";
import { useState } from "react";
import { useIntl } from "react-intl";
import { modeChange } from "../../reducers/article-mode";
import store from "../../store";
import { ArticleMode } from "./Article";

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
          if (mode === "read" || newMode === "read") {
            onModeChange(newMode);
          }
        }
        setMode(newMode);
        //发布mode变更
        store.dispatch(modeChange(newMode as ArticleMode));
      }}
    />
  );
};

export default ModeSwitchWidget;
