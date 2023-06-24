import { Dropdown } from "antd";
import React from "react";

interface IWidget {
  label?: React.ReactNode;
  onMenuClick?: Function;
}

const ToolButtonNavSliceTitleWidget = ({ label, onMenuClick }: IWidget) => {
  return (
    <Dropdown.Button
      type="text"
      trigger={["click"]}
      menu={{
        items: [
          {
            key: "copy-link",
            label: "复制链接",
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
