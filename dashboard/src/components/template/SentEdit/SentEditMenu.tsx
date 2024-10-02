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
  ReloadOutlined,
} from "@ant-design/icons";
import type { MenuProps } from "antd";
import { ISentence } from "../SentEdit";
import SentHistoryModal from "../../corpus/SentHistoryModal";
import {
  CommentOutlinedIcon,
  HandOutlinedIcon,
  JsonOutlinedIcon,
  MergeIcon2,
  PasteOutLinedIcon,
} from "../../../assets/icon";
import { useIntl } from "react-intl";
import { fullUrl } from "../../../utils";

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
      case "refresh":
        break;
      case "copy-link":
        if (data) {
          let link = `/article/para/${data.book}-${data.para}?mode=edit`;
          link += `&book=${data.book}&par=${data.para}`;
          link += `&channel=${data.channel.id}`;

          link += `&focus=${data.book}-${data.para}-${data.wordStart}-${data.wordEnd}`;
          navigator.clipboard.writeText(fullUrl(link)).then(() => {
            message.success("链接地址已经拷贝到剪贴板");
          });
        }
        break;
      default:
        break;
    }
  };
  const items: MenuProps["items"] = [
    {
      key: "refresh",
      label: intl.formatMessage({
        id: "buttons.refresh",
      }),
      icon: <ReloadOutlined />,
    },
    {
      key: "timeline",
      label: intl.formatMessage({
        id: "buttons.timeline",
      }),
      icon: <FieldTimeOutlined />,
      disabled: isPr,
    },
    {
      key: "copy-to",
      label: intl.formatMessage({
        id: "buttons.copy.to",
      }),
      icon: <MergeIcon2 />,
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
      disabled:
        !data ||
        data.channel.type !== "nissaya" ||
        data.contentType === "json" ||
        isPr,
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

  const buttonStyle = { backgroundColor: "rgba(1,1,1,0)", marginRight: 2 };

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
          padding: 4,
          border: "1px solid black",
          borderRadius: 4,
          backgroundColor: "rgb(239 239 206)",
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
            style={buttonStyle}
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
            style={buttonStyle}
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
            style={buttonStyle}
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
          <Button icon={<MoreOutlined />} size="small" style={buttonStyle} />
        </Dropdown>
      </div>
      <div
        style={{
          border: isHover ? "1px solid black" : "1px solid  rgba(1,1,1,0)",
          borderRadius: 4,
        }}
      >
        {children}
      </div>
    </div>
  );
};

export default SentEditMenuWidget;
