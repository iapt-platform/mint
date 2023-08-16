import { useIntl } from "react-intl";
import {
  Button,
  Card,
  Dropdown,
  message,
  Modal,
  Space,
  Typography,
} from "antd";
import {
  MoreOutlined,
  EditOutlined,
  DeleteOutlined,
  LinkOutlined,
  CommentOutlined,
  MessageOutlined,
  ExclamationCircleOutlined,
  CloseOutlined,
} from "@ant-design/icons";
import type { MenuProps } from "antd";

import { IComment } from "./DiscussionItem";
import TimeShow from "../general/TimeShow";
import Marked from "../general/Marked";
import { delete_ } from "../../request";
import { IDeleteResponse } from "../api/Article";
import { fullUrl } from "../../utils";

const { Text } = Typography;

interface IWidget {
  data: IComment;
  onEdit?: Function;
  onSelect?: Function;
  onDelete?: Function;
  onReply?: Function;
  onClose?: Function;
}
const DiscussionShowWidget = ({
  data,
  onEdit,
  onSelect,
  onDelete,
  onReply,
  onClose,
}: IWidget) => {
  const intl = useIntl();
  const showDeleteConfirm = (id: string, title: string) => {
    Modal.confirm({
      icon: <ExclamationCircleOutlined />,
      title:
        intl.formatMessage({
          id: "message.delete.sure",
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
        console.log("delete", id);
        return delete_<IDeleteResponse>(`/v2/discussion/${id}`)
          .then((json) => {
            if (json.ok) {
              message.success("删除成功");
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
  const onClick: MenuProps["onClick"] = (e) => {
    console.log("click ", e);
    switch (e.key) {
      case "copy-link":
        let url = `/discussion/topic/`;
        if (data.parent) {
          url += `${data.parent}#${data.id}`;
        } else {
          url += data.id;
        }
        navigator.clipboard.writeText(fullUrl(url)).then(() => {
          message.success("链接地址已经拷贝到剪贴板");
        });
        break;
      case "reply":
        if (typeof onReply !== "undefined") {
          onReply();
        }
        break;
      case "edit":
        if (typeof onEdit !== "undefined") {
          onEdit();
        }
        break;
      case "close":
        if (typeof onClose !== "undefined") {
          onClose();
        }
        break;
      case "delete":
        if (data.id) {
          showDeleteConfirm(data.id, data.title ? data.title : "");
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
    },
    {
      key: "reply",
      label: intl.formatMessage({
        id: "buttons.reply",
      }),
      icon: <CommentOutlined />,
      disabled: data.parent ? true : false,
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
      disabled: data.status === "close",
    },
    {
      key: "reopen",
      label: intl.formatMessage({
        id: "buttons.open",
      }),
      icon: <CloseOutlined />,
      disabled: data.status === "active",
    },
    {
      key: "delete",
      label: intl.formatMessage({
        id: "buttons.delete",
      }),
      icon: <DeleteOutlined />,
      danger: true,
      disabled: data.childrenCount ? true : false,
    },
    {
      type: "divider",
    },
    {
      key: "report-content",
      label: "举报",
    },
  ];
  return (
    <Card
      size="small"
      title={
        <Space direction="vertical">
          {data.title ? (
            <Text
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
          <Text type="secondary" style={{ display: "none" }}>
            <Space>
              {data.user.nickName}
              <TimeShow
                type="secondary"
                updatedAt={data.updatedAt}
                createdAt={data.createdAt}
              />
            </Space>
          </Text>
        </Space>
      }
      extra={
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
            <Button
              shape="circle"
              size="small"
              icon={<MoreOutlined />}
            ></Button>
          </Dropdown>
        </Space>
      }
      style={{ width: "100%" }}
    >
      <Marked text={data.content} />
    </Card>
  );
};

export default DiscussionShowWidget;
