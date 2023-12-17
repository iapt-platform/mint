import { SettingOutlined } from "@ant-design/icons";
import SettingArticle from "../auth/setting/SettingArticle";

import ToolButton from "./ToolButton";
import { useIntl } from "react-intl";

interface IWidget {
  type?: string;
  articleId?: string;
}
const ToolButtonSettingWidget = ({ type, articleId }: IWidget) => {
  const intl = useIntl();
  return (
    <ToolButton
      title={intl.formatMessage({
        id: `buttons.setting`,
      })}
      icon={<SettingOutlined />}
      content={<SettingArticle />}
    />
  );
};

export default ToolButtonSettingWidget;
