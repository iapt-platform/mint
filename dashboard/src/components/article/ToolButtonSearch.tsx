import { SearchOutlined } from "@ant-design/icons";

import ToolButton from "./ToolButton";

interface IWidget {
  type?: string;
  articleId?: string;
}
const ToolButtonSearchWidget = ({ type, articleId }: IWidget) => {
  const id = articleId?.split("_");
  let tocWidget = <></>;
  if (id && id.length > 0) {
    const sentId = id[0].split("-");
    if (sentId.length > 1) {
      tocWidget = <></>;
    }
  }

  return (
    <ToolButton title="搜索" icon={<SearchOutlined />} content={tocWidget} />
  );
};

export default ToolButtonSearchWidget;
