import { Button, Space, Typography } from "antd";
import { useEffect, useState } from "react";
import { DoubleRightOutlined, DoubleLeftOutlined } from "@ant-design/icons";

import { get } from "../../request";
import { ArticleType } from "./Article";

const { Paragraph } = Typography;

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
    <Paragraph style={{ display: "flex", justifyContent: "space-between" }}>
      <Button
        disabled={typeof prev === "undefined"}
        size="large"
        onClick={(event: React.MouseEvent<HTMLElement, MouseEvent>) => {
          if (typeof onChange !== "undefined" && prev) {
            onChange(event, prev.id);
          }
        }}
      >
        <Space>
          <DoubleLeftOutlined />
          {prev?.title}
        </Space>
      </Button>
      <Button
        size="large"
        disabled={typeof next === "undefined"}
        onClick={(event: React.MouseEvent<HTMLElement, MouseEvent>) => {
          if (typeof onChange !== "undefined" && next) {
            onChange(event, next.id);
          }
        }}
      >
        <Space>
          {next?.title}
          <DoubleRightOutlined />
        </Space>
      </Button>
    </Paragraph>
  );
};

export default NavigateWidget;
