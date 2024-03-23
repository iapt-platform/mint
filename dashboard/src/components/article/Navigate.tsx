import { Button, Space, Typography } from "antd";
import { useEffect, useState } from "react";
import { DoubleRightOutlined, DoubleLeftOutlined } from "@ant-design/icons";

import { get } from "../../request";
import { ArticleType } from "./Article";
import React from "react";
import NavigateButton from "./NavigateButton";
import { ITocPathNode } from "../corpus/TocPath";

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
  path?: ITocPathNode[];
  onChange?: Function;
  onPathChange?: Function;
}
const NavigateWidget = ({
  type,
  articleId,
  path,
  onChange,
  onPathChange,
}: IWidget) => {
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
    <NavigateButton
      prevTitle={prev?.title}
      nextTitle={next?.title}
      path={path}
      onPrev={(event: React.MouseEvent<HTMLElement, MouseEvent>) => {
        if (typeof onChange !== "undefined" && prev) {
          onChange(event, prev.id);
        }
      }}
      onNext={(event: React.MouseEvent<HTMLElement, MouseEvent>) => {
        if (typeof onChange !== "undefined" && next) {
          onChange(event, next.id);
        }
      }}
      onPathChange={(key: string) => {
        if (typeof onPathChange !== "undefined") {
          onPathChange(key);
        }
      }}
    />
  );
};

export default NavigateWidget;
