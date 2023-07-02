import { Button, Dropdown } from "antd";
import { useState } from "react";
import {
  EditOutlined,
  CopyOutlined,
  MoreOutlined,
  FieldTimeOutlined,
  LinkOutlined,
} from "@ant-design/icons";
import type { MenuProps } from "antd";
import { ISentence } from "../SentEdit";
import SentHistoryModal from "../../corpus/SentHistoryModal";

interface IWidget {
  data: ISentence;
  children?: React.ReactNode;
  onModeChange?: Function;
  onConvert?: Function;
}
const SentEditMenuWidget = ({
  data,
  children,
  onModeChange,
  onConvert,
}: IWidget) => {
  const [isHover, setIsHover] = useState(false);
  const [timelineOpen, setTimelineOpen] = useState(false);

  console.log("data", data);
  const onClick: MenuProps["onClick"] = (e) => {
    console.log(e);
    switch (e.key) {
      case "json":
        if (typeof onConvert !== "undefined") {
          onConvert("json");
        }
        break;
      case "markdown":
        if (typeof onConvert !== "undefined") {
          onConvert("markdown");
        }
        break;
      case "timeline":
        setTimelineOpen(true);
        break;
      default:
        break;
    }
  };
  const items: MenuProps["items"] = [
    {
      key: "timeline",
      label: "时间线",
      icon: <FieldTimeOutlined />,
    },
    {
      type: "divider",
    },
    {
      key: "markdown",
      label: "Markdown",
      disabled: data.contentType === "markdown",
    },
    {
      key: "json",
      label: "Json",
      disabled: data.contentType === "json",
    },
    {
      type: "divider",
    },
    {
      key: "copy-link",
      label: "复制链接",
      icon: <LinkOutlined />,
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
      <SentHistoryModal
        open={timelineOpen}
        onClose={() => setTimelineOpen(false)}
        sentId={data.id}
      />
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
