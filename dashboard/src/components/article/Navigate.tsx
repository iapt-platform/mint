import { Affix, Button, Space, Typography } from "antd";
import { useEffect, useState } from "react";
import { DoubleRightOutlined, DoubleLeftOutlined } from "@ant-design/icons";

import { get } from "../../request";
import { ArticleType } from "./Article";

const { Paragraph, Text } = Typography;

const EllipsisMiddle: React.FC<{
  suffixCount: number;
  maxWidth: number;
  children?: string;
}> = ({ suffixCount, maxWidth = 500, children = "" }) => {
  const start = children.slice(0, children.length - suffixCount).trim();
  const suffix = children.slice(-suffixCount).trim();
  return (
    <Text style={{ maxWidth: maxWidth }} ellipsis={{ suffix }}>
      {start}
    </Text>
  );
};

interface INavButton {
  title: string;
  subtitle: string;
  id: string;
}
interface INavResponse {
  ok: boolean;
  message: string;
  data: {
    prev?: INavButton;
    next?: INavButton;
  };
}

interface IWidget {
  type?: ArticleType;
  articleId?: string;
  onChange?: Function;
}
const NavigateWidget = ({ type, articleId, onChange }: IWidget) => {
  const [prev, setPrev] = useState<INavButton>();
  const [next, setNext] = useState<INavButton>();

  useEffect(() => {
    if (type && articleId) {
      get<INavResponse>(`/v2/article-nav?type=${type}&id=${articleId}`).then(
        (json) => {
          if (json.ok) {
            setPrev(json.data.prev);
            setNext(json.data.next);
          }
        }
      );
    }
  }, [articleId, type]);
  return (
    <Affix offsetBottom={0}>
      <Paragraph
        style={{
          display: "flex",
          justifyContent: "space-between",
          backdropFilter: "blur(5px)",
          backgroundColor: "rgba(50,50,50,0.2)",
          padding: 4,
        }}
      >
        <Button
          disabled={typeof prev === "undefined"}
          size="middle"
          onClick={(event: React.MouseEvent<HTMLElement, MouseEvent>) => {
            if (typeof onChange !== "undefined" && prev) {
              onChange(event, prev.id);
            }
          }}
        >
          <Space>
            <DoubleLeftOutlined />
            <EllipsisMiddle maxWidth={300} suffixCount={7}>
              {prev?.title}
            </EllipsisMiddle>
          </Space>
        </Button>
        <Button
          size="middle"
          disabled={typeof next === "undefined"}
          onClick={(event: React.MouseEvent<HTMLElement, MouseEvent>) => {
            if (typeof onChange !== "undefined" && next) {
              onChange(event, next.id);
            }
          }}
        >
          <Space>
            <EllipsisMiddle maxWidth={300} suffixCount={7}>
              {next?.title}
            </EllipsisMiddle>
            <DoubleRightOutlined />
          </Space>
        </Button>
      </Paragraph>
    </Affix>
  );
};

export default NavigateWidget;
