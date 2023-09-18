import { useIntl } from "react-intl";
import { useEffect, useState } from "react";
import { Button, Tag, Tree } from "antd";
import { ReloadOutlined } from "@ant-design/icons";

import ToolButton from "./ToolButton";
import { post } from "../../request";
import { IUser } from "../auth/User";
import { CommentOutlinedIcon } from "../../assets/icon";

interface IPrTreeData {
  book: number;
  paragraph: number;
  word_start: number;
  word_end: number;
  channel_id: string;
  content: string;
  pr_count: number;
}
interface IPrTreeRequestData {
  book: number;
  paragraph: number;
  word_start: number;
  word_end: number;
  channel_id: string;
}
interface IPrData {
  title: string;
  children_count: number;
  editor?: IUser;
}
interface IPrTreeRequest {
  data: IPrTreeRequestData[];
}
interface IPrTreeResponseData {
  sentence: IPrTreeData;
  pr: IPrData[];
}
interface IPrTreeResponse {
  ok: boolean;
  message: string;
  data: { rows: IPrTreeResponseData[]; count: number };
}
interface DataNode {
  title: string;
  key: string;
  isLeaf?: boolean;
  childrenCount?: number;
  children?: DataNode[];
}

interface IWidget {
  type?: string;
  articleId?: string;
}
const ToolButtonDiscussionWidget = ({ type, articleId }: IWidget) => {
  const [treeData, setTreeData] = useState<DataNode[]>([]);
  const [loading, setLoading] = useState(false);
  const intl = useIntl();

  const refresh = () => {
    const pr = document.querySelectorAll("div.tran_sent");

    let prRequestData: IPrTreeRequestData[] = [];
    for (let index = 0; index < pr.length; index++) {
      const element = pr[index];
      const id = element.id.split("_");
      prRequestData.push({
        book: parseInt(id[0]),
        paragraph: parseInt(id[1]),
        word_start: parseInt(id[2]),
        word_end: parseInt(id[3]),
        channel_id: id[4],
      });
    }
    console.log("request pr tree", prRequestData);
    setLoading(true);
    post<IPrTreeRequest, IPrTreeResponse>("/v2/sent-discussion-tree", {
      data: prRequestData,
    })
      .then((json) => {
        console.log("discussion tree", json);
        if (json.ok) {
          const newTree: DataNode[] = json.data.rows.map((item) => {
            const children: DataNode[] = item.pr.map((pr) => {
              return {
                title: pr.title,
                key: pr.title,
                childrenCount: pr.children_count,
              };
            });
            return {
              title: item.sentence.content,
              key: `${item.sentence.book}_${item.sentence.paragraph}_${item.sentence.word_start}_${item.sentence.word_end}_${item.sentence.channel_id}`,
              children: children,
            };
          });
          setTreeData(newTree);
        }
      })
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    //refresh();
  }, []);

  return (
    <ToolButton
      title="讨论"
      icon={<CommentOutlinedIcon />}
      content={
        <>
          <div style={{ display: "flex", justifyContent: "space-between" }}>
            <span></span>
            <Button
              type="text"
              icon={<ReloadOutlined />}
              loading={loading}
              onClick={() => {
                refresh();
              }}
            >
              {intl.formatMessage({
                id: "buttons.refresh",
              })}
            </Button>
          </div>

          {treeData.length === 0 ? (
            <>没有数据</>
          ) : (
            <Tree
              treeData={treeData}
              titleRender={(node) => {
                const ele = document.getElementById(node.key);
                const count = node.childrenCount
                  ? node.childrenCount
                  : node.children?.length;
                return (
                  <div
                    onClick={() => {
                      ele?.scrollIntoView();
                    }}
                  >
                    {node.title}
                    <Tag style={{ borderRadius: 5 }}>{count}</Tag>
                  </div>
                );
              }}
            />
          )}
        </>
      }
    />
  );
};

export default ToolButtonDiscussionWidget;
