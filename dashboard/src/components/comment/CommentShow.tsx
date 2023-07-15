import { useIntl } from "react-intl";
import { Button, Card, Dropdown, Space } from "antd";
import { MoreOutlined } from "@ant-design/icons";
import type { MenuProps } from "antd";

import { IComment } from "./CommentItem";
import TimeShow from "../general/TimeShow";

interface IWidget {
  data: IComment;
  onEdit?: Function;
  onSelect?: Function;
}
const CommentShowWidget = ({ data, onEdit, onSelect }: IWidget) => {
  const intl = useIntl();
  const onClick: MenuProps["onClick"] = (e) => {
    console.log("click ", e);
    switch (e.key) {
      case "edit":
        if (typeof onEdit !== "undefined") {
          onEdit();
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
    },
    {
      key: "reply",
      label: "回复",
    },
    {
      type: "divider",
    },
    {
      key: "edit",
      label: "编辑",
    },
    {
      key: "delete",
      label: "删除",
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
        <Space>
          {data.user.nickName}
          <TimeShow
            time={data.updatedAt}
            title={intl.formatMessage({
              id: "labels.updated-at",
            })}
          />
        </Space>
      }
      extra={
        <Dropdown menu={{ items, onClick }} placement="bottomRight">
          <Button shape="circle" size="small" icon={<MoreOutlined />}></Button>
        </Dropdown>
      }
      style={{ width: "100%" }}
    >
      <span
        onClick={(e) => {
          if (typeof onSelect !== "undefined") {
            onSelect();
          }
        }}
      >
        {data.content}
      </span>
    </Card>
  );
};

export default CommentShowWidget;
