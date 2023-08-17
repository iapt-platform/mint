import { Button, Dropdown, message } from "antd";
import { useState } from "react";
import {
  EditOutlined,
  CopyOutlined,
  MoreOutlined,
  FieldTimeOutlined,
  LinkOutlined,
  CommentOutlined,
  FileMarkdownOutlined,
} from "@ant-design/icons";
import type { MenuProps } from "antd";
import { ISentence } from "../SentEdit";
import SentHistoryModal from "../../corpus/SentHistoryModal";
import { HandOutlinedIcon, JsonOutlinedIcon } from "../../../assets/icon";
import { useIntl } from "react-intl";

interface IWidget {
  data: ISentence;
  children?: React.ReactNode;
  onModeChange?: Function;
  onConvert?: Function;
  onMenuClick?: Function;
}
const SentEditMenuWidget = ({
  data,
  children,
  onModeChange,
  onConvert,
  onMenuClick,
}: IWidget) => {
  const [isHover, setIsHover] = useState(false);
  const [timelineOpen, setTimelineOpen] = useState(false);
  const intl = useIntl();

  const onClick: MenuProps["onClick"] = (e) => {
    if (typeof onMenuClick !== "undefined") {
      onMenuClick(e.key);
    }
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
      key: "suggestion",
      label: "suggestion",
      icon: <HandOutlinedIcon />,
    },
    {
      key: "discussion",
      label: "discussion",
      icon: <CommentOutlined />,
    },
    {
      type: "divider",
    },
    {
      key: "markdown",
      label: "To Markdown",
      icon: <FileMarkdownOutlined />,
      disabled: data.contentType === "markdown",
    },
    {
      key: "json",
      label: "To Json",
      icon: <JsonOutlinedIcon />,
      disabled: data.contentType === "json",
    },
    {
      type: "divider",
    },
    {
      key: "copy-link",
      label: intl.formatMessage({
        id: "buttons.copy.link",
      }),
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
          marginTop: 0,
          right: 30,
          position: "absolute",
          display: isHover ? "block" : "none",
        }}
      >
        <Button
          icon={<EditOutlined />}
          size="small"
          title="edit"
          onClick={() => {
            if (typeof onModeChange !== "undefined") {
              onModeChange("edit");
            }
          }}
        />
        <Button
          icon={<CopyOutlined />}
          size="small"
          onClick={() => {
            if (data.content) {
              navigator.clipboard.writeText(data.content).then(() => {
                message.success("已经拷贝到剪贴板");
              });
            } else {
              message.success("内容为空");
            }
          }}
        />
        <Dropdown menu={{ items, onClick }} placement="bottomRight">
          <Button icon={<MoreOutlined />} size="small" />
        </Dropdown>
      </div>
      {children}
    </div>
  );
};

export default SentEditMenuWidget;
