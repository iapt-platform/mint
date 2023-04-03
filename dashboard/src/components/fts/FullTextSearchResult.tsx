import { List, Space, Tag, Typography } from "antd";
import { useEffect, useState } from "react";
import { Link } from "react-router-dom";

import { get } from "../../request";
import TocPath, { ITocPathNode } from "../corpus/TocPath";
import Marked from "../general/Marked";
import "./search.css";

const { Title, Text } = Typography;

interface IFtsData {
  rank: number;
  highlight: string;
  book: number;
  paragraph: number;
  content: string;
  title?: string;
  paliTitle?: string;
  path?: ITocPathNode[];
}
interface IFtsResponse {
  ok: boolean;
  string: string;
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
}
const Widget = ({
  keyWord,
  tags,
  bookId,
  book,
  para,
  orderBy,
  match,
  keyWord2,
}: IWidget) => {
  const [ftsData, setFtsData] = useState<IFtsItem[]>();

  const [total, setTotal] = useState<number>();
  const [currPage, setCurrPage] = useState<number>(1);

  useEffect(() => {
    let url = `/v2/search?key=${keyWord}`;
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
    const offset = (currPage - 1) * 10;
    url += `&limit=10&offset=${offset}`;
    console.log("fetch url", url);
    get<IFtsResponse>(url).then((json) => {
      if (json.ok) {
        const result: IFtsItem[] = json.data.rows.map((item) => {
          return {
            book: item.book,
            paragraph: item.paragraph,
            title: item.title ? item.title : item.paliTitle,
            paliTitle: item.paliTitle,
            content: item.highlight.replaceAll("** ti ", "**ti "),
            path: item.path,
          };
        });
        setFtsData(result);
        setTotal(json.data.count);
      }
    });
  }, [bookId, currPage, keyWord, match, orderBy, tags]);
  return (
    <List
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
      renderItem={(item) => (
        <List.Item>
          <Title level={5}>
            <Link to={`/article/para?book=${item.book}&par=${item.paragraph}`}>
              {item.title}
            </Link>
          </Title>
          <div>
            <Text type="secondary">{item.paliTitle}</Text>
          </div>
          <Space>
            <TocPath data={item.path} />
            {"/"}
            <Tag>{item.paragraph}</Tag>
          </Space>
          <div className="search_content">
            <Marked text={item.content} />
          </div>
        </List.Item>
      )}
    />
  );
};

export default Widget;
