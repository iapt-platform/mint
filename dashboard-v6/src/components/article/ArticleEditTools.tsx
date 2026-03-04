import { Link } from "react-router";
import { useIntl } from "react-intl";
import { TeamOutlined } from "@ant-design/icons";
import { Button, Space } from "antd";

import ShareModal from "../share/ShareModal";
import TplBuilder from "../tpl-builder/TplBuilder";
import AddToAnthology from "../anthology/AddToAnthology";
import { EResType } from "../share/utils";

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
      <TplBuilder trigger={<Button type="link">{"<t>"}</Button>} />
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
      <TplBuilder
        title={title}
        tpl="article"
        articleId={articleId}
        trigger={<Button type="link">获取模版</Button>}
      />
    </Space>
  );
};

export default ArticleEditToolsWidget;
