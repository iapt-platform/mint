import { Link } from "react-router-dom";
import { useIntl } from "react-intl";
import { TeamOutlined } from "@ant-design/icons";
import { Button, Space } from "antd";

import { ArticleTplModal } from "../template/Builder/ArticleTpl";
import ShareModal from "../../components/share/ShareModal";
import { EResType } from "../../components/share/Share";
import AddToAnthology from "../../components/article/AddToAnthology";
import Builder from "../template/Builder/Builder";

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
      <Builder trigger={<Button type="link">{"<t>"}</Button>} />
      {articleId ? (
        <AddToAnthology
          trigger={<Button type="link">加入文集</Button>}
          studioName={studioName}
          articleIds={[articleId]}
        />
      ) : undefined}
      {articleId ? (
        <ShareModal
          trigger={
            <Button type="link" icon={<TeamOutlined />}>
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
        {intl.formatMessage({ id: "buttons.open.in.tab" })}
      </Link>
      <ArticleTplModal
        title={title}
        type="article"
        id={articleId}
        trigger={<Button type="link">获取模版</Button>}
      />
    </Space>
  );
};

export default ArticleEditToolsWidget;
