import { useEffect, useState } from "react";
import { useNavigate, useParams } from "react-router";
import { useIntl } from "react-intl";
import { Button, Card, Space, Tabs } from "antd";
import { ExportOutlined, TeamOutlined } from "@ant-design/icons";

import ChapterInChannelList from "../../../components/channel/ChapterInChannelList";
import ShareModal from "../../../components/share/ShareModal";

import { articlePath, fullUrl, libraryUrl } from "../../../utils";
import type { IArticleParam } from "../../../types/article";
import { EResType } from "../../../components/share/utils";
import { fetchChannel } from "../../../api/channel";
import TermList from "../../../components/term/TermList";

const Widget = () => {
  const { channelId } = useParams(); //url 参数
  const navigate = useNavigate();
  const [title, setTitle] = useState<string>();
  const intl = useIntl();
  const channelTitle = intl.formatMessage({
    id: "columns.studio.channel.title",
  });
  // const [articleOpen, setArticleOpen] = useState(false);
  //const [param, setParam] = useState<IArticleParam>();

  useEffect(() => {
    if (channelId) {
      // 只为标签页标题；取不到就用默认标题（页面本身由路由 loader 负责报错）
      fetchChannel(channelId).then(
        (channel) => setTitle(channel.name),
        () => setTitle(undefined),
      );
    }
  }, [channelId]);
  return (
    <>
      <title>{title ? `${channelTitle}-${title}` : channelTitle}</title>
      <Card
        title={title}
        extra={
          channelId ? (
            <Space>
              <Button
                icon={<ExportOutlined />}
                href={libraryUrl(`tipitaka/channels/${channelId}`)}
                target="_blank"
              >
                {intl.formatMessage({
                  id: "buttons.open.in.library",
                })}
              </Button>
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
            </Space>
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
                    chapter: IArticleParam,
                  ) => {
                    if (event.ctrlKey || event.metaKey) {
                      let url = `${articlePath(chapter.type, chapter.articleId)}?mode=`;
                      url += chapter?.mode ? chapter?.mode : "read";
                      url += chapter?.channelId
                        ? `&channel=${chapter.channelId}`
                        : "";
                      window.open(fullUrl(url), "_blank");
                    } else {
                      let url = articlePath(chapter.type, chapter.articleId);
                      url += chapter?.channelId
                        ? `?channel=${chapter.channelId}`
                        : "";
                      navigate(url);
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
        {/**
      <ArticleDrawer
        {...param}
        open={articleOpen}
        onClose={() => setArticleOpen(false)}
      />
   */}
      </Card>
    </>
  );
};

export default Widget;
