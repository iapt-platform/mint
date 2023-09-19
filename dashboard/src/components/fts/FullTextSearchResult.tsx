import { List, Skeleton, Space, Tag, Typography } from "antd";
import { useEffect, useState } from "react";
import { Link } from "react-router-dom";

import { get } from "../../request";
import TocPath, { ITocPathNode } from "../corpus/TocPath";
import { TContentType } from "../discussion/DiscussionCreate";
import Marked from "../general/Marked";
import PaliText from "../template/Wbw/PaliText";
import "./search.css";

const { Title, Text } = Typography;

interface IFtsData {
  rank?: number;
  highlight?: string;
  book: number;
  paragraph: number;
  content?: string;
  content_type?: TContentType;
  title?: string;
  paliTitle?: string;
  path?: ITocPathNode[];
}
interface IFtsResponse {
  ok: boolean;
  message: string;
  data: {
    rows: IFtsData[];
    count: number;
  };
}
interface IFtsItem {
  book: number;
  paragraph: number;
  title?: string;
  paliTitle?: string;
  content?: string;
  path?: ITocPathNode[];
}
interface IWidget {
  keyWord?: string;
  tags?: string[];
  bookId?: string | null;
  book?: number;
  para?: number;
  orderBy?: string | null;
  match?: string | null;
  keyWord2?: string;
  view?: string;
  pageType?: string;
}
const FullTxtSearchResultWidget = ({
  keyWord,
  tags,
  bookId,
  book,
  para,
  orderBy,
  match,
  keyWord2,
  view = "pali",
  pageType,
}: IWidget) => {
  const [ftsData, setFtsData] = useState<IFtsItem[]>();
  const [total, setTotal] = useState<number>();
  const [loading, setLoading] = useState(false);
  const [currPage, setCurrPage] = useState<number>(1);

  useEffect(() => {
    let url = `/v2/search?view=${view}&key=${keyWord}`;
    if (typeof tags !== "undefined") {
      url += `&tags=${tags}`;
    }
    if (bookId) {
      url += `&book=${bookId}`;
    }
    if (orderBy) {
      url += `&orderby=${orderBy}`;
    }
    if (match) {
      url += `&match=${match}`;
    }
    if (pageType) {
      url += `&type=${pageType}`;
    }
    const offset = (currPage - 1) * 10;
    url += `&limit=10&offset=${offset}`;
    console.log("fetch url", url);
    setLoading(true);
    get<IFtsResponse>(url)
      .then((json) => {
        if (json.ok) {
          const result: IFtsItem[] = json.data.rows.map((item) => {
            return {
              book: item.book,
              paragraph: item.paragraph,
              title: item.title ? item.title : item.paliTitle,
              paliTitle: item.paliTitle,
              content: item.highlight
                ? item.highlight.replaceAll("** ti ", "**ti ")
                : item.content,
              path: item.path,
            };
          });
          setFtsData(result);
          setTotal(json.data.count);
        } else {
          console.error(json.message);
        }
      })
      .finally(() => setLoading(false));
  }, [bookId, currPage, keyWord, match, orderBy, pageType, tags, view]);
  return (
    <List
      style={{ width: "100%" }}
      itemLayout="vertical"
      size="small"
      dataSource={ftsData}
      pagination={{
        onChange: (page) => {
          console.log(page);
          setCurrPage(page);
        },
        showQuickJumper: true,
        showSizeChanger: false,
        pageSize: 10,
        total: total,
        position: "both",
        showTotal: (total) => {
          return `结果: ${total}`;
        },
      }}
      renderItem={(item) => {
        const paragraph = [
          item.paragraph - 1,
          item.paragraph,
          item.paragraph + 1,
        ];
        return (
          <List.Item>
            {loading ? (
              <div style={{ width: "100%" }}>
                <Skeleton active />
              </div>
            ) : (
              <div>
                <div>
                  <PaliText text={item.path ? item.path[0].title : ""} />
                </div>
                <div>
                  <Space style={{ color: "gray", fontSize: "80%" }}>
                    <TocPath
                      data={item.path?.slice(1)}
                      style={{ fontSize: "80%" }}
                    />
                    {"/"}
                    <Tag style={{ fontSize: "80%" }}>{item.paragraph}</Tag>
                  </Space>
                </div>
                <Title level={4} style={{ fontWeight: 500 }}>
                  <Link
                    to={`/article/para/${item.book}-${item.paragraph}?book=${item.book}&par=${paragraph}&focus=${item.paragraph}`}
                  >
                    {item.title}
                  </Link>
                </Title>
                <div style={{ display: "none" }}>
                  <Text type="secondary">{item.paliTitle}</Text>
                </div>

                <div>
                  <Marked className="search_content" text={item.content} />
                </div>
              </div>
            )}
          </List.Item>
        );
      }}
    />
  );
};

export default FullTxtSearchResultWidget;
