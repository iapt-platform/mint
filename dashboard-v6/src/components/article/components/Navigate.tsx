import { useEffect, useState } from "react";

import React from "react";
import NavigateButton from "./NavigateButton";
import type { ArticleType } from "../../../api/Article";
import type { ITocPathNode } from "../../../api/pali-text";
import { get } from "../../../request";

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
  onChange?: (
    event: React.MouseEvent<HTMLElement, MouseEvent>,
    id: string
  ) => void;
  onPathChange?: (key: string) => void;
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
      get<INavResponse>(
        `/api/v2/article-nav?type=${type}&id=${articleId}`
      ).then((json) => {
        if (json.ok) {
          setPrev(json.data.prev);
          setNext(json.data.next);
        }
      });
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
