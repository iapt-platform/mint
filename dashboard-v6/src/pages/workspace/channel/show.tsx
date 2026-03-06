import { useEffect, useState } from "react";
import { useParams } from "react-router";
import { useIntl } from "react-intl";
import { Button, Card, Tabs } from "antd";
import { TeamOutlined } from "@ant-design/icons";

import { get } from "../../../request";

import ChapterInChannelList from "../../../components/channel/ChapterInChannelList";
import ShareModal from "../../../components/share/ShareModal";

import { fullUrl } from "../../../utils";
import type { IArticleParam } from "../../../types/article";
import { EResType } from "../../../components/share/utils";
import type { IApiResponseChannel } from "../../../api/channel";

const Widget = () => {
  const { channelId } = useParams(); //url 参数
  const [title, setTitle] = useState<string>();
  const intl = useIntl();
  // const [articleOpen, setArticleOpen] = useState(false);
  //const [param, setParam] = useState<IArticleParam>();

  useEffect(() => {
    get<IApiResponseChannel>(`/api/v2/channel/${channelId}`).then((json) => {
      console.debug("channel fetch", json.data);
      setTitle(json.data.name);
    });
  }, [channelId]);
  return (
    <>
      <title>{"channel-" + title}</title>
      <Card
        title={title}
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
                      //setParam(chapter);
                      //setArticleOpen(true);
                    }
                  }}
                />
              ),
            },
            {
              label: `term`,
              key: "term",
              //children: <TermList channelId={channelId} />,
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
