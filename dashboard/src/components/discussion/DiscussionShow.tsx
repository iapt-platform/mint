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
} from "@ant-design/icons";
import type { MenuProps } from "antd";

import { IComment } from "./DiscussionItem";
import TimeShow from "../general/TimeShow";
import Marked from "../general/Marked";
import { delete_ } from "../../request";
import { IDeleteResponse } from "../api/Article";

const { Text } = Typography;

interface IWidget {
  data: IComment;
  onEdit?: Function;
  onSelect?: Function;
  onDelete?: Function;
}
const DiscussionShowWidget = ({
  data,
  onEdit,
  onSelect,
  onDelete,
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
      case "edit":
        if (typeof onEdit !== "undefined") {
          onEdit();
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
      label: "复制链接",
      icon: <LinkOutlined />,
    },
    {
      key: "reply",
      label: "回复",
      icon: <CommentOutlined />,
      disabled: data.parent ? true : false,
    },
    {
      type: "divider",
    },
    {
      key: "edit",
      label: "编辑",
      icon: <EditOutlined />,
    },
    {
      key: "delete",
      label: "删除",
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
          <Text
            strong
            onClick={(e) => {
              if (typeof onSelect !== "undefined") {
                onSelect(e, data);
              }
            }}
          >
            {data.title}
          </Text>
          <Text type="secondary">
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
          <Dropdown menu={{ items, onClick }} placement="bottomRight">
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
