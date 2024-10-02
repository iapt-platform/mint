import { useIntl } from "react-intl";
import {
  Button,
  Card,
  Dropdown,
  message,
  Modal,
  notification,
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

import { IComment } from "./DiscussionItem";
import TimeShow from "../general/TimeShow";
import Marked from "../general/Marked";
import { delete_, put } from "../../request";
import { IDeleteResponse } from "../api/Article";
import { fullUrl } from "../../utils";
import { ICommentRequest, ICommentResponse } from "../api/Comment";
import { useState } from "react";
import MdView from "../template/MdView";
import { TDiscussionType } from "./Discussion";
import { discussionCountUpgrade } from "./DiscussionCount";

const { Text } = Typography;

interface IWidget {
  data: IComment;
  hideTitle?: boolean;
  onEdit?: Function;
  onSelect?: Function;
  onDelete?: Function;
  onReply?: Function;
  onClose?: Function;
  onConvert?: Function;
}
const DiscussionShowWidget = ({
  data,
  hideTitle = false,
  onEdit,
  onSelect,
  onDelete,
  onReply,
  onClose,
  onConvert,
}: IWidget) => {
  const intl = useIntl();
  const [closed, setClosed] = useState(data.status);
  const showDeleteConfirm = (id: string, resId: string, title: string) => {
    Modal.confirm({
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
      onOk() {
        const url = `/v2/discussion/${id}`;
        console.info("Discussion delete api request", url);
        return delete_<IDeleteResponse>(url)
          .then((json) => {
            console.debug("api response", json);
            if (json.ok) {
              message.success("删除成功");
              discussionCountUpgrade(resId);
              if (typeof onDelete !== "undefined") {
                onDelete(id);
              }
            } else {
              message.error(json.message);
            }
          })
          .catch((e) => console.log("Oops errors!", e));
      },
    });
  };

  const close = (value: boolean) => {
    const url = `/v2/discussion/${data.id}`;
    const newData: ICommentRequest = {
      title: data.title,
      content: data.content,
      status: value ? "close" : "active",
    };
    console.info("api request", url, newData);
    put<ICommentRequest, ICommentResponse>(url, newData).then((json) => {
      console.log(json);
      if (json.ok) {
        setClosed(json.data.status);
        discussionCountUpgrade(data.resId);
        if (typeof onClose !== "undefined") {
          onClose(value);
        }
      } else {
        message.error(json.message);
      }
    });
  };

  const convert = (newType: TDiscussionType) => {
    const url = `/v2/discussion/${data.id}`;
    const newData: ICommentRequest = {
      title: data.title,
      content: data.content,
      status: data.status,
      type: newType,
    };
    console.info("api response", url, newData);
    put<ICommentRequest, ICommentResponse>(url, newData).then((json) => {
      console.info("api response", json);
      if (json.ok) {
        notification.info({ message: "转换成功" });
        if (typeof onConvert !== "undefined") {
          onConvert(newType);
        }
      }
    });
  };

  const onClick: MenuProps["onClick"] = (e) => {
    console.log("click ", e);
    switch (e.key) {
      case "copy-link":
        let url = `/discussion/topic/`;
        if (data.id) {
          if (data.parent) {
            url += `${data.parent}#${data.id}`;
          } else {
            url += data.id;
          }
        } else {
          url += `${data.tplId}?tpl=true&resId=${data.resId}&resType=${data.resType}`;
        }

        navigator.clipboard.writeText(fullUrl(url)).then(() => {
          message.success("链接地址已经拷贝到剪贴板");
        });
        break;
      case "copy-tpl":
        const tpl = `{{qa|id=${data.id}|style=collapse}}`;
        navigator.clipboard.writeText(tpl).then(() => {
          notification.success({ message: "链接地址已经拷贝到剪贴板" });
        });
        break;
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
      case "delete":
        if (data.id && data.resId) {
          showDeleteConfirm(data.id, data.resId, data.title ?? "");
        }
        break;
      default:
        break;
    }
  };
  console.log("children", data.childrenCount);
  const items: MenuProps["items"] = [
    {
      key: "copy-link",
      label: intl.formatMessage({
        id: "buttons.copy.link",
      }),
      icon: <LinkOutlined />,
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
    },
    {
      key: "close",
      label: intl.formatMessage({
        id: "buttons.close",
      }),
      icon: <CloseOutlined />,
      disabled: closed === "close",
    },
    {
      key: "reopen",
      label: intl.formatMessage({
        id: "buttons.open",
      }),
      icon: <CheckOutlined />,
      disabled: closed === "active",
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
      disabled: data.parent ? true : false,
      children: [
        { key: "convert_qa", label: "qa", disabled: data.type === "qa" },
        { key: "convert_help", label: "help", disabled: data.type === "help" },
        {
          key: "convert_discussion",
          label: "discussion",
          disabled: data.type === "discussion",
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
      disabled: data.childrenCount && data.childrenCount > 0 ? true : false,
    },
  ];

  const editInfo = () => {
    return (
      <Space direction="vertical" size={"small"}>
        {data.title && !hideTitle ? (
          <Text
            style={{ fontSize: 16 }}
            strong
            onClick={(e) => {
              if (typeof onSelect !== "undefined") {
                onSelect(e);
              }
            }}
          >
            {data.title}
          </Text>
        ) : undefined}
        <Text type="secondary" style={{ fontSize: "80%" }}>
          <Space>
            {!data.parent && closed === "close" ? (
              <Tag style={{ backgroundColor: "#8250df", color: "white" }}>
                {"closed"}
              </Tag>
            ) : undefined}
            {data.user.nickName}
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
          <Marked text={data.content} />
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
