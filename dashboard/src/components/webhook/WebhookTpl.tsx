import { Button, Space, Tag, Tree } from "antd";
import { useEffect, useState } from "react";
import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";
import ArticleListModal from "../article/ArticleListModal";

export interface IWebhookEvent {
  key: string;
  tpl?: string;
  tplTitle?: string;
}

interface DataNode {
  title: string;
  key: string;
  isLeaf?: boolean;
  children?: DataNode[];
}

interface IWidget {
  value?: IWebhookEvent[];
  onChange?: Function;
}
const WebhookTplWidget = ({ value = [], onChange }: IWidget) => {
  const [event, setEvent] = useState(value);
  const user = useAppSelector(currentUser);

  useEffect(() => {
    if (typeof onChange !== "undefined") {
      onChange(event);
    }
  }, [event]);

  const treeData: DataNode[] = [
    {
      title: "事件",
      key: "event",
      children: [
        {
          title: "discussion",
          key: "discussion",
          children: [
            {
              title: "create",
              key: "discussion-create",
              isLeaf: true,
            },
            {
              title: "replay",
              key: "discussion-replay",
              isLeaf: true,
            },
            {
              title: "edit",
              key: "discussion-edit",
              isLeaf: true,
            },
          ],
        },
        {
          title: "pr",
          key: "pr",
          children: [
            {
              title: "create",
              key: "pr-create",
              isLeaf: true,
            },
            {
              title: "edit",
              key: "pr-edit",
              isLeaf: true,
            },
          ],
        },
        {
          title: "content",
          key: "content",
          children: [
            {
              title: "create",
              key: "content-create",
              isLeaf: true,
            },
            {
              title: "edit",
              key: "content-edit",
              isLeaf: true,
            },
          ],
        },
      ],
    },
  ];

  return (
    <Tree
      treeData={treeData}
      checkable
      titleRender={(node) => {
        const tpl = event?.find((value) => value.key === node.key);
        return (
          <Space>
            {node.title}
            {node.key === "event" ? `自定义模版${event.length}` : ""}
            {node.isLeaf ? (
              <ArticleListModal
                studioName={user?.realName}
                trigger={
                  <Button type="text">
                    {tpl ? (
                      <Tag
                        closable
                        onClose={() => {
                          setEvent((origin) => {
                            const index = origin.findIndex(
                              (value) => value.key === node.key
                            );
                            if (index >= 0) {
                              origin.splice(index, 1);
                              console.log("origin", origin);
                            }
                            return origin;
                          });
                        }}
                      >
                        {tpl.tplTitle}
                      </Tag>
                    ) : (
                      "默认模版"
                    )}
                  </Button>
                }
                multiple={false}
                onSelect={(id: string, title: string) => {
                  setEvent((origin) => {
                    const index = origin.findIndex(
                      (value) => value.key === node.key
                    );
                    if (index >= 0) {
                      origin[index].tpl = id;
                      origin[index].tplTitle = title;
                      return origin;
                    } else {
                      return [
                        ...origin,
                        { key: node.key, tpl: id, tplTitle: title },
                      ];
                    }
                  });
                }}
              />
            ) : (
              ""
            )}
          </Space>
        );
      }}
    />
  );
};

export default WebhookTplWidget;
