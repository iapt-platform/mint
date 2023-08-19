import { Link } from "react-router-dom";
import { useIntl } from "react-intl";
import { TeamOutlined } from "@ant-design/icons";
import { Button, Space } from "antd";

import { ArticleTplModal } from "../template/Builder/ArticleTpl";
import ShareModal from "../../components/share/ShareModal";
import { EResType } from "../../components/share/Share";
import AddToAnthology from "../../components/article/AddToAnthology";

interface IWidget {
  studioName?: string;
  articleId?: string;
  title?: string;
}
const ArticleEditToolsWidget = ({
  studioName,
  articleId,
  title = "title",
}: IWidget) => {
  const intl = useIntl();
  return (
    <Space>
      {articleId ? (
        <AddToAnthology studioName={studioName} articleIds={[articleId]} />
      ) : undefined}
      {articleId ? (
        <ShareModal
          trigger={
            <Button icon={<TeamOutlined />}>
              {intl.formatMessage({
                id: "buttons.share",
              })}
            </Button>
          }
          resId={articleId}
          resType={EResType.article}
        />
      ) : undefined}
      <Link to={`/article/article/${articleId}`} target="_blank">
        {intl.formatMessage({ id: "buttons.open.in.library" })}
      </Link>
      <ArticleTplModal
        title={title}
        type="article"
        id={articleId}
        trigger={<Button>获取模版</Button>}
      />
    </Space>
  );
};

export default ArticleEditToolsWidget;
