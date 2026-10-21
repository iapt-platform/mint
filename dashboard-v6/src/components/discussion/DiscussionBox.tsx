import { useEffect, useState } from "react";

import { useAppSelector } from "../../hooks";
import {
  type IShowDiscussion,
  message,
  setHighlight,
  show,
  showAnchor,
} from "../../reducers/discussion";
import { Button, Segmented } from "antd";
import store from "../../store";
import Discussion from "./Discussion";
import ChapterDiscussionList from "./ChapterDiscussionList";
import type { IComment } from "../../api/discussion";
import type { ArticleType } from "../../api/article";

type TScope = "sentence" | "chapter";

interface IWidget {
  /** 右侧面板所在页面的文章；章节页（chapter，id 为 book-para）才有「本章」视图 */
  articleId?: string;
  articleType?: ArticleType;
  /** 页面打开的版本，多个用 "_" 拼接 */
  channelId?: string | null;
}

const DiscussionBoxWidget = ({ articleId, articleType, channelId }: IWidget) => {
  const [topicId, setTopicId] = useState<string>();
  const [currTopic, setCurrTopic] = useState<IComment>();

  const discussionMessage = useAppSelector(message);

  // 章节页：book-para
  const [book, chapter] =
    articleType === "chapter" && articleId
      ? articleId.split("-").map((item) => parseInt(item))
      : [NaN, NaN];
  const hasChapter = Number.isInteger(book) && Number.isInteger(chapter);
  const channels = channelId ? channelId.split("_").filter(Boolean) : [];

  // 章节页默认看本章；点了某一句的讨论（或从本章列表点开一条）就切到本句
  const [scope, setScope] = useState<TScope>(
    hasChapter && !discussionMessage?.resId ? "chapter" : "sentence"
  );
  // 消息变了：切到本句、打开消息里点名的话题（React「prop 变化时调整 state」模式）
  const [prevMessage, setPrevMessage] = useState(discussionMessage);
  if (prevMessage !== discussionMessage) {
    setPrevMessage(discussionMessage);
    if (discussionMessage?.resId) {
      setScope("sentence");
    }
    if (discussionMessage?.topic) {
      setTopicId(discussionMessage.topic);
    }
  }

  // 「本章」列表选中的高亮只在本章视图里有效：切走或面板关掉就清掉
  useEffect(() => {
    if (scope !== "chapter") return;
    return () => {
      store.dispatch(setHighlight(undefined));
    };
  }, [scope]);

  /** 让正文滚到这一句（SentCell 监听 anchor） */
  const locate = (resId?: string) => {
    if (!resId) return;
    store.dispatch(showAnchor({ type: "discussion", resId, resType: "sentence" }));
  };

  return (
    <>
      {hasChapter ? (
        <Segmented<TScope>
          block
          style={{ marginBottom: 8 }}
          value={scope}
          onChange={setScope}
          options={[
            { value: "sentence", label: "本句" },
            { value: "chapter", label: "本章" },
          ]}
        />
      ) : undefined}
      {scope === "chapter" && hasChapter ? (
        <ChapterDiscussionList
          book={book}
          chapter={chapter}
          channels={channels}
          onSelect={(topic) => {
            store.dispatch(setHighlight(topic?.resId));
            locate(topic?.resId);
          }}
          onOpen={(topic) => {
            if (!topic.resId) return;
            // 切到这一句并打开话题：复用单句讨论的详情、回复、编辑
            const info: IShowDiscussion = {
              type: "discussion",
              resId: topic.resId,
              resType: "sentence",
              topic: topic.id,
            };
            store.dispatch(show(info));
            locate(topic.resId);
          }}
        />
      ) : (
        <>
          <Button
            type="link"
            onClick={() => {
              const anchorInfo: IShowDiscussion = {
                type: "discussion",
                resId: discussionMessage?.resId
                  ? discussionMessage?.resId
                  : currTopic?.resId,
                resType: discussionMessage?.resType,
              };
              store.dispatch(show(anchorInfo));
              store.dispatch(showAnchor(anchorInfo));
            }}
          >
            显示译文
          </Button>
          <Discussion
            resId={discussionMessage?.resId}
            resType={discussionMessage?.resType}
            focus={discussionMessage?.comment}
            showTopicId={topicId}
            onTopicReady={(value: IComment) => {
              setCurrTopic(value);
            }}
            onBack={() => setTopicId(undefined)}
          />
        </>
      )}
    </>
  );
};

export default DiscussionBoxWidget;
