import { Button, Dropdown, Tooltip, message } from "antd";
import { useState } from "react";
import {
  EditOutlined,
  CopyOutlined,
  MoreOutlined,
  FieldTimeOutlined,
  LinkOutlined,
  FileMarkdownOutlined,
  DeleteOutlined,
} from "@ant-design/icons";
import type { MenuProps } from "antd";
import { ISentence } from "../SentEdit";
import SentHistoryModal from "../../corpus/SentHistoryModal";
import {
  CommentOutlinedIcon,
  HandOutlinedIcon,
  JsonOutlinedIcon,
  PasteOutLinedIcon,
} from "../../../assets/icon";
import { useIntl } from "react-intl";

interface IWidget {
  data?: ISentence;
  children?: React.ReactNode;
  isPr?: boolean;
  onModeChange?: Function;
  onConvert?: Function;
  onMenuClick?: Function;
}
const SentEditMenuWidget = ({
  data,
  children,
  isPr = false,
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
      label: intl.formatMessage({
        id: "buttons.timeline",
      }),
      icon: <FieldTimeOutlined />,
      disabled: isPr,
    },
    {
      type: "divider",
    },
    {
      key: "suggestion",
      label: "suggestion",
      icon: <HandOutlinedIcon />,
      disabled: isPr,
    },
    {
      key: "discussion",
      label: "discussion",
      icon: <CommentOutlinedIcon />,
      disabled: isPr,
    },
    {
      type: "divider",
    },
    {
      key: "markdown",
      label: "To Markdown",
      icon: <FileMarkdownOutlined />,
      disabled: !data || data.contentType === "markdown" || isPr,
    },
    {
      key: "json",
      label: "To Json",
      icon: <JsonOutlinedIcon />,
      disabled: !data || data.contentType === "json" || isPr,
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
    {
      key: "delete",
      label: intl.formatMessage({
        id: "buttons.delete",
      }),
      icon: <DeleteOutlined />,
      danger: true,
      disabled: !isPr,
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
        sentId={data?.id}
      />
      <div
        style={{
          marginTop: -22,
          right: 30,
          position: "absolute",
          display: isHover ? "block" : "none",
        }}
      >
        <Tooltip
          title={intl.formatMessage({
            id: "buttons.edit",
          })}
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
        </Tooltip>
        <Tooltip
          title={intl.formatMessage({
            id: "buttons.copy",
          })}
        >
          <Button
            icon={<CopyOutlined />}
            size="small"
            onClick={() => {
              if (data?.content) {
                navigator.clipboard.writeText(data.content).then(() => {
                  message.success("已经拷贝到剪贴板");
                });
              } else {
                message.success("内容为空");
              }
            }}
          />
        </Tooltip>
        <Tooltip
          title={intl.formatMessage({
            id: "buttons.paste",
          })}
        >
          <Button
            icon={<PasteOutLinedIcon />}
            size="small"
            onClick={() => {
              if (typeof onMenuClick !== "undefined") {
                onMenuClick("paste");
              }
            }}
          />
        </Tooltip>
        <Dropdown
          disabled={data ? false : true}
          menu={{ items, onClick }}
          placement="bottomRight"
        >
          <Button icon={<MoreOutlined />} size="small" />
        </Dropdown>
      </div>
      {children}
    </div>
  );
};

export default SentEditMenuWidget;
