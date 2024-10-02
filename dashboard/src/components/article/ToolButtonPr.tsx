import { useIntl } from "react-intl";
import { useEffect, useState } from "react";
import { Button, Tag, Tree } from "antd";
import { ReloadOutlined } from "@ant-design/icons";

import { HandOutlinedIcon } from "../../assets/icon";
import ToolButton from "./ToolButton";
import { post } from "../../request";
import { IUser } from "../auth/User";

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
  content: string;
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
  children?: DataNode[];
}

interface IWidget {
  type?: string;
  articleId?: string;
}
const ToolButtonPrWidget = ({ type, articleId }: IWidget) => {
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
    post<IPrTreeRequest, IPrTreeResponse>("/v2/sent-pr-tree", {
      data: prRequestData,
    })
      .then((json) => {
        console.log("pr tree", json);
        if (json.ok) {
          const newTree: DataNode[] = json.data.rows.map((item) => {
            const children = item.pr.map((pr) => {
              return { title: pr.content, key: pr.content };
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
      title="修改建议"
      icon={<HandOutlinedIcon />}
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
          <Tree
            treeData={treeData}
            titleRender={(node) => {
              const ele = document.getElementById(node.key);
              const count = node.children?.length;
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
        </>
      }
    />
  );
};

export default ToolButtonPrWidget;
