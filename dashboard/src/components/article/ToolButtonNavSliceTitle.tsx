import { Dropdown } from "antd";
import React from "react";
import { useIntl } from "react-intl";

interface IWidget {
  label?: React.ReactNode;
  onMenuClick?: Function;
}

const ToolButtonNavSliceTitleWidget = ({ label, onMenuClick }: IWidget) => {
  const intl = useIntl();
  return (
    <Dropdown.Button
      type="text"
      trigger={["click"]}
      menu={{
        items: [
          {
            key: "copy-link",
            label: intl.formatMessage({
              id: "buttons.copy.link",
            }),
          },
          {
            key: "open",
            label: "打开",
          },
        ],
        onClick: (e) => {
          if (typeof onMenuClick !== "undefined") {
            onMenuClick(e.key);
          }
        },
      }}
    >
      <>{label}</>
    </Dropdown.Button>
  );
};

export default ToolButtonNavSliceTitleWidget;
