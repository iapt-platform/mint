import { SettingOutlined } from "@ant-design/icons";
import SettingArticle from "../auth/setting/SettingArticle";

import ToolButton from "./ToolButton";

interface IWidget {
  type?: string;
  articleId?: string;
}
const Widget = ({ type, articleId }: IWidget) => {
  return (
    <ToolButton
      title="设置"
      icon={<SettingOutlined />}
      content={<SettingArticle />}
    />
  );
};

export default Widget;
