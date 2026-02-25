import type { JSX } from "react";
import type { TDisplayStyle } from "../../types/template";

interface IWidget {
  url?: string;
  title?: string;
  style?: TDisplayStyle;
}
export const VideoTplMock = ({ title }: IWidget) => {
  return <>{title}</>;
};

interface IModalWidget {
  url?: string;
  title?: string;
  style?: TDisplayStyle;
  trigger?: JSX.Element;
}
export const VideoTplModalMock = ({ trigger }: IModalWidget) => {
  return <>{trigger}</>;
};
