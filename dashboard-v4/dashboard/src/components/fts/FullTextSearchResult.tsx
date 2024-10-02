import { Button, List, Skeleton, Space, Tag, Typography } from "antd";
import { useEffect, useState } from "react";
import { Link } from "react-router-dom";

import { get } from "../../request";
import TocPath, { ITocPathNode } from "../corpus/TocPath";
import { TContentType } from "../discussion/DiscussionCreate";
import Marked from "../general/Marked";
import PaliText from "../template/Wbw/PaliText";
import "./search.css";
import AiTranslate from "../ai/AiTranslate";

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
  rank?: number;
}

export type ISearchView = "pali" | "title" | "page" | "number";
interface IWidget {
  keyWord?: string;
  keyWords?: string[];
  engin?: "wbw" | "tulip";
  tags?: string[];
  bookId?: string | null;
  book?: number;
  para?: number;
  bold?: string | null;
  orderBy?: string | null;
  match?: string | null;
  keyWord2?: string;
  view?: ISearchView;
  pageType?: string;
}
const FullTxtSearchResultWidget = ({
  keyWord,
  keyWords,
  engin = "wbw",
  tags,
  bookId,
  book,
  para,
  orderBy,
  match,
  bold,
  keyWord2,
  view = "pali",
  pageType,
}: IWidget) => {
  const [ftsData, setFtsData] = useState<IFtsItem[]>();
  const [total, setTotal] = useState<number>();
  const [loading, setLoading] = useState(false);
  const [currPage, setCurrPage] = useState<number>(1);

  useEffect(
    () => setCurrPage(1),
    [view, keyWord, keyWords, tags, bookId, match, pageType, bold]
  );

  useEffect(() => {
    /**
     * 搜索引擎选择逻辑
     * 如果 keyWord 包涵空格 使用 tulip
     * 如果 keyWord 不包涵空格 使用 wbw
     */
    let words;
    let api = "";
    if (keyWord?.trim().includes(" ")) {
      api = "search";
      words = keyWord;
    } else {
      api = "search-pali-wbw";
      words = keyWords?.join();
    }

    let url = `/v2/${api}?view=${view}&key=${words}`;
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
    if (bold) {
      url += `&bold=${bold}`;
    }
    const offset = (currPage - 1) * 10;
    url += `&limit=10&offset=${offset}`;
    console.log("fetch url", url);
    setLoading(true);
    get<IFtsResponse>(url)
      .then((json) => {
        if (json.ok) {
          console.log("data", json.data);
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
              rank: item.rank,
            };
          });
          setFtsData(result);
          setTotal(json.data.count);
        } else {
          console.error(json.message);
        }
      })
      .finally(() => setLoading(false));
  }, [
    bookId,
    currPage,
    keyWord,
    keyWords,
    match,
    orderBy,
    pageType,
    tags,
    view,
    bold,
  ]);
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
        let paragraph: number[];
        if (view === "title") {
          paragraph = [item.paragraph, item.paragraph + 1, item.paragraph + 2];
        } else {
          paragraph = [item.paragraph - 1, item.paragraph, item.paragraph + 1];
        }
        let link: string = "";
        switch (view) {
          case "pali":
            link = `/article/para/${item.book}-${item.paragraph}?book=${item.book}&par=${paragraph}&focus=${item.paragraph}`;
            break;
          case "title":
            link = `/article/chapter/${item.book}-${item.paragraph}`;
            break;
          case "page":
            link = `/article/chapter/${item.book}-${item.paragraph}`;
            break;
          default:
            break;
        }
        let title = "unnamed";
        if (item.paliTitle) {
          if (item.paliTitle.length > 0) {
            title = item.paliTitle;
          }
        }
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
                  <Link to={link} target="_blank">
                    {item.title ? item.title : title}
                  </Link>
                </Title>
                <div style={{ display: "none" }}>
                  <Text type="secondary">{item.paliTitle}</Text>
                </div>

                <div>
                  <Marked className="search_content" text={item.content} />
                </div>
                <div>
                  <AiTranslate
                    paragraph={`${item.book}-${item.paragraph}`}
                    trigger
                  />
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
