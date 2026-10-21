import { useIntl } from "react-intl";
import {
  App,
  Button,
  Card,
  Dropdown,
  Space,
  Tag,
  Typography,
} from "antd";
import {
  MoreOutlined,
  EditOutlined,
  DeleteOutlined,
  LinkOutlined,
  CheckOutlined,
  MessageOutlined,
  ExclamationCircleOutlined,
  CloseOutlined,
  SyncOutlined,
} from "@ant-design/icons";
import type { MenuProps } from "antd";

import TimeShow from "../general/TimeShow";
import Marked from "../general/Marked";
import { fullUrl } from "../../utils";
import { useState } from "react";
import {
  deleteDiscussion,
  updateDiscussion,
  type IComment,
  type TDiscussionType,
} from "../../api/discussion";
import { refreshDiscussionBadge } from "./utils";
import MdView from "../general/MdView";
import { SeverityTag } from "./SeverityTag";

const { Text } = Typography;

interface IWidget {
  data: IComment;
  hideTitle?: boolean;
  onEdit?: () => void;
  onSelect?: (
    e: React.MouseEvent<HTMLElement, MouseEvent>,
    value: IComment
  ) => void;
  onDelete?: (id: string) => void;
  onClose?: (value: boolean) => void;
  onConvert?: (type: TDiscussionType) => void;
}
const DiscussionShowWidget = ({
  data,
  hideTitle = false,
  onEdit,
  onSelect,
  onDelete,
  onClose,
  onConvert,
}: IWidget) => {
  const intl = useIntl();
  const { message, notification, modal } = App.useApp();
  const [closed, setClosed] = useState(data.status);
  const showDeleteConfirm = (id: string, resId: string, title: string) => {
    modal.confirm({
      icon: <ExclamationCircleOutlined />,
      title:
        intl.formatMessage({
          id: "message.delete.confirm",
        }) +
        intl.formatMessage({
          id: "message.irrevocable",
        }),

      content: title,
      okText: intl.formatMessage({
        id: "buttons.delete",
      }),
      okType: "danger",
      cancelText: intl.formatMessage({
        id: "buttons.no",
      }),
      onOk: async () => {
        await deleteDiscussion(id);
        message.success("删除成功");
        refreshDiscussionBadge(data.resType, resId);
        onDelete?.(id);
      },
    });
  };

  const close = async (value: boolean) => {
    if (!data.id) {
      return;
    }
    const updated = await updateDiscussion(data.id, {
      status: value ? "close" : "active",
    });
    setClosed(updated.status);
    refreshDiscussionBadge(data.resType, data.resId);
    onClose?.(value);
  };

  const convert = async (newType: TDiscussionType) => {
    if (!data.id) {
      return;
    }
    await updateDiscussion(data.id, { type: newType });
    notification.info({ message: "转换成功" });
    onConvert?.(newType);
  };

  const onClick: MenuProps["onClick"] = (e) => {
    console.log("click ", e);
    switch (e.key) {
      case "copy-link": {
        if (!data.id) {
          break;
        }
        // 回复用 #锚点 定位，话题页据此滚动高亮
        const url = data.parent
          ? `/workspace/discussion/topic/${data.parent}#${data.id}`
          : `/workspace/discussion/topic/${data.id}`;

        navigator.clipboard.writeText(fullUrl(url)).then(() => {
          message.success("链接地址已经拷贝到剪贴板");
        });
        break;
      }
      case "copy-tpl": {
        const tpl = `{{qa|id=${data.id}|style=collapse}}`;
        navigator.clipboard.writeText(tpl).then(() => {
          notification.success({ message: "链接地址已经拷贝到剪贴板" });
        });
        break;
      }
      case "edit":
        if (typeof onEdit !== "undefined") {
          onEdit();
        }
        break;
      case "close":
        close(true);
        break;
      case "reopen":
        close(false);
        break;
      case "convert_qa":
        convert("qa");
        break;
      case "convert_help":
        convert("help");
        break;
      case "convert_discussion":
        convert("discussion");
        break;
      case "convert_issue":
        convert("issue");
        break;
      case "convert_glossary":
        convert("glossary");
        break;
      case "delete":
        if (data.id && data.resId) {
          showDeleteConfirm(data.id, data.resId, data.title ?? "");
        }
        break;
      default:
        break;
    }
  };
  const items: MenuProps["items"] = [
    {
      key: "copy-link",
      label: intl.formatMessage({
        id: "buttons.copy.link",
      }),
      icon: <LinkOutlined />,
      disabled: !data.id,
    },
    {
      key: "copy-tpl",
      label: intl.formatMessage({
        id: "buttons.copy.tpl",
      }),
      icon: <LinkOutlined />,
      disabled: data.type !== "qa",
    },
    {
      type: "divider",
    },
    {
      key: "edit",
      label: intl.formatMessage({
        id: "buttons.edit",
      }),
      icon: <EditOutlined />,
      disabled: !data.abilities.update,
    },
    {
      key: "close",
      label: intl.formatMessage({
        id: "buttons.close",
      }),
      icon: <CloseOutlined />,
      disabled: !data.abilities.close || closed === "close",
    },
    {
      key: "reopen",
      label: intl.formatMessage({
        id: "buttons.open",
      }),
      icon: <CheckOutlined />,
      disabled: !data.abilities.close || closed === "active",
    },
    {
      type: "divider",
    },
    {
      key: "convert",
      label: intl.formatMessage({
        id: "buttons.convert",
      }),
      icon: <SyncOutlined />,
      disabled: data.parent || !data.abilities.update ? true : false,
      children: [
        { key: "convert_qa", label: "qa", disabled: data.type === "qa" },
        { key: "convert_help", label: "help", disabled: data.type === "help" },
        {
          key: "convert_discussion",
          label: "discussion",
          disabled: data.type === "discussion",
        },
        { key: "convert_issue", label: "issue", disabled: data.type === "issue" },
        {
          key: "convert_glossary",
          label: "glossary",
          disabled: data.type === "glossary",
        },
      ],
    },
    {
      type: "divider",
    },
    {
      key: "delete",
      label: intl.formatMessage({
        id: "buttons.delete",
      }),
      icon: <DeleteOutlined />,
      danger: true,
      // 软删除：话题有回复也能删，删掉的话题留作墓碑，回复照常显示
      disabled: !data.abilities.delete,
    },
  ];

  const editInfo = () => {
    return (
      <Space orientation="vertical" size={"small"}>
        {data.title && !hideTitle ? (
          <Text
            style={{ fontSize: 16 }}
            strong
            onClick={(e) => {
              if (typeof onSelect !== "undefined") {
                onSelect(e, data);
              }
            }}
          >
            {data.title}
          </Text>
        ) : undefined}
        <Text type="secondary" style={{ fontSize: "80%" }}>
          <Space>
            <SeverityTag severity={data.severity} />
            {!data.parent && closed === "close" ? (
              <Tag style={{ backgroundColor: "#8250df", color: "white" }}>
                {"closed"}
              </Tag>
            ) : undefined}
            {data.user.nickName}
            {data.coEditor ? (
              <Text type="secondary">（与 {data.coEditor.nickName}）</Text>
            ) : undefined}
            <TimeShow
              type="secondary"
              updatedAt={data.updatedAt}
              createdAt={data.createdAt}
            />
          </Space>
        </Text>
      </Space>
    );
  };

  const editMenu = () => {
    return (
      <Space>
        <span
          style={{
            display: data.childrenCount === 0 ? "none" : "inline",
            cursor: "pointer",
          }}
          onClick={(e) => {
            if (typeof onSelect !== "undefined") {
              onSelect(e, data);
            }
          }}
        >
          {data.childrenCount ? (
            <>
              <MessageOutlined /> {data.childrenCount}
            </>
          ) : undefined}
        </span>
        <Dropdown
          menu={{ items, onClick }}
          placement="bottomRight"
          trigger={["click"]}
        >
          <Button shape="circle" size="small" icon={<MoreOutlined />}></Button>
        </Dropdown>
      </Space>
    );
  };
  if (data.deleted) {
    return (
      <Card size="small" style={{ width: "100%" }}>
        <Text type="secondary" italic>
          （已删除）
        </Text>
      </Card>
    );
  }
  return (
    <Card
      size="small"
      title={data.type === "qa" && data.parent ? undefined : editInfo()}
      extra={data.type === "qa" && data.parent ? undefined : editMenu()}
      style={{ width: "100%" }}
    >
      <div>
        {data.html ? (
          <MdView html={data.html} />
        ) : (
          <Marked text={data.content ?? undefined} />
        )}
      </div>
      {data.type === "qa" && data.parent ? (
        <div style={{ display: "flex", justifyContent: "space-between" }}>
          <div></div>
          <div>
            {editInfo()}
            {editMenu()}
          </div>
        </div>
      ) : (
        <></>
      )}
    </Card>
  );
};

export default DiscussionShowWidget;
