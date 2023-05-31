import { Button, Dropdown } from "antd";
import { useState } from "react";
import { EditOutlined, CopyOutlined, MoreOutlined } from "@ant-design/icons";
import type { MenuProps } from "antd";

interface ISentEditMenu {
  children?: React.ReactNode;
  onModeChange?: Function;
  onConvert?: Function;
}
const SentEditMenuWidget = ({
  children,
  onModeChange,
  onConvert,
}: ISentEditMenu) => {
  const [isHover, setIsHover] = useState(false);

  const onClick: MenuProps["onClick"] = (e) => {
    console.log(e);
    switch (e.key) {
      case "json":
        if (typeof onConvert !== "undefined") {
          onConvert("json");
        }
        break;

      default:
        break;
    }
  };
  const items = [
    {
      key: "timeline",
      label: "时间线",
    },
    {
      key: "convert",
      label: "转换格式",
      children: [
        {
          key: "markdown",
          label: "markdown",
        },
        {
          key: "json",
          label: "json",
        },
      ],
    },
    {
      key: "share",
      label: "分享",
    },
  ];

  return (
    <div
      onMouseEnter={() => {
        setIsHover(true);
      }}
      onMouseLeave={() => {
        setIsHover(false);
      }}
    >
      <div
        style={{
          marginTop: "-1.2em",
          right: "0",
          position: "absolute",
          display: isHover ? "block" : "none",
        }}
      >
        <Button
          icon={<EditOutlined />}
          size="small"
          onClick={() => {
            if (typeof onModeChange !== "undefined") {
              onModeChange("edit");
            }
          }}
        />
        <Button icon={<CopyOutlined />} size="small" />
        <Dropdown menu={{ items, onClick }} placement="bottomRight">
          <Button icon={<MoreOutlined />} size="small" />
        </Dropdown>
      </div>
      {children}
    </div>
  );
};

export default SentEditMenuWidget;
