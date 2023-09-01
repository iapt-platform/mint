import { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { Button, Card, Tabs } from "antd";
import { TeamOutlined } from "@ant-design/icons";

import { get } from "../../../request";
import GoBack from "../../../components/studio/GoBack";
import { IApiResponseChannel } from "../../../components/api/Channel";
import ChapterInChannelList from "../../../components/channel/ChapterInChannelList";
import TermList from "../../../components/term/TermList";
import ShareModal from "../../../components/share/ShareModal";
import { useIntl } from "react-intl";
import { EResType } from "../../../components/share/Share";
import { IArticleParam } from "../recent/list";
import ArticleDrawer from "../../../components/article/ArticleDrawer";
import { fullUrl } from "../../../utils";

const Widget = () => {
  const { channelId } = useParams(); //url 参数
  const { studioname } = useParams();
  const [title, setTitle] = useState("");
  const intl = useIntl();
  const [articleOpen, setArticleOpen] = useState(false);
  const [param, setParam] = useState<IArticleParam>();

  useEffect(() => {
    get<IApiResponseChannel>(`/v2/channel/${channelId}`).then((json) => {
      setTitle(json.data.name);
      document.title = `${json.data.name}`;
    });
  }, [channelId]);
  return (
    <Card
      title={<GoBack to={`/studio/${studioname}/channel/list`} title={title} />}
      extra={
        channelId ? (
          <ShareModal
            trigger={
              <Button icon={<TeamOutlined />}>
                {intl.formatMessage({
                  id: "buttons.share",
                })}
              </Button>
            }
            resId={channelId}
            resType={EResType.channel}
          />
        ) : undefined
      }
    >
      <Tabs
        size="small"
        items={[
          {
            label: `chapter`,
            key: "chapter",
            children: (
              <ChapterInChannelList
                channelId={channelId}
                onSelect={(
                  event: React.MouseEvent<HTMLElement, MouseEvent>,
                  chapter: IArticleParam
                ) => {
                  if (event.ctrlKey || event.metaKey) {
                    let url = `/article/${chapter.type}/${chapter.articleId}?mode=`;
                    url += chapter?.mode ? chapter?.mode : "read";
                    url += chapter?.channelId
                      ? `&channel=${chapter.channelId}`
                      : "";
                    window.open(fullUrl(url), "_blank");
                  } else {
                    setParam(chapter);
                    setArticleOpen(true);
                  }
                }}
              />
            ),
          },
          {
            label: `term`,
            key: "term",
            children: <TermList channelId={channelId} />,
          },
        ]}
      />
      <ArticleDrawer
        {...param}
        open={articleOpen}
        onClose={() => setArticleOpen(false)}
      />
    </Card>
  );
};

export default Widget;
