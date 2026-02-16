import { TagOutlined } from "@ant-design/icons";

import ToolButton from "./ToolButton";

interface IWidget {
  type?: string;
  articleId?: string;
}
const ToolButtonTagWidget = ({ type, articleId }: IWidget) => {
  const id = articleId?.split("_");
  let tocWidget = <></>;
  if (id && id.length > 0) {
    const sentId = id[0].split("-");
    if (sentId.length > 1) {
      tocWidget = <></>;
    }
  }

  return <ToolButton title="标签" icon={<TagOutlined />} content={tocWidget} />;
};

export default ToolButtonTagWidget;
