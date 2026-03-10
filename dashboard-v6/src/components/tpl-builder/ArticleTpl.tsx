import type { JSX } from "react";
import type { ArticleType } from "../../api/article";
import type { TDisplayStyle } from "../../types/template";

interface IWidget {
  type?: ArticleType;
  articleId?: string;
  title?: string;
  style?: TDisplayStyle;
  channel?: string | null;
}

export const ArticleTplMock = ({ title }: IWidget) => {
  return <span>{"ArticleTplMock" + title}</span>;
};

interface IModalWidget {
  open?: boolean;
  type?: ArticleType;
  articleId?: string;
  channelsId?: string | null;
  title?: string;
  style?: TDisplayStyle;
  trigger?: JSX.Element;
  onClose?: () => void;
}
export const ArticleTplModalMock = ({ trigger }: IModalWidget) => {
  return <>{trigger}</>;
};
