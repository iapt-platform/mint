import { List, Skeleton } from "antd";

import { useEffect, useState } from "react";

import { get } from "../../request";

import DiscussionCreate from "./DiscussionCreate";
import {
  fetchReplies,
  type IComment,
  type TResType,
} from "../../api/discussion";
import type {
  ISentHistoryData,
  ISentHistoryListResponse,
} from "../../api/sentence-history";
import DiscussionItem from "./DiscussionItem";
import SentHistoryGroup from "../sentence-history/SentHistoryGroup";

interface IItem {
  type: "comment" | "sent";
  comment?: IComment;
  sent?: ISentHistoryData[];
  oldSent?: string;
  date: number;
}

interface IWidget {
  topic?: IComment;
  resId?: string;
  resType?: TResType;
  topicId?: string;
  focus?: string;
  hideReply?: boolean;
  onItemCountChange?: (total: number, parentId?: string | null) => void;
  onTopicCreate?: (value: IComment) => void;
}
const DiscussionTopicChildrenWidget = ({
  topic,
  resId,
  resType,
  topicId,
  focus,
  hideReply = false,
  onItemCountChange,
  onTopicCreate,
}: IWidget) => {
  const [data, setData] = useState<IComment[]>([]);
  const [loading, setLoading] = useState(false);
  const [history, setHistory] = useState<ISentHistoryData[]>([]);
  const [items, setItems] = useState<IItem[]>();

  useEffect(() => {
    if (loading === false) {
      const ele = document.getElementById(`answer-${focus}`);
      ele?.scrollIntoView({
        behavior: "smooth",
        block: "center",
      });
      console.log("after render");
    }
  });

  useEffect(() => {
    const comment: IItem[] = data.map((item) => {
      const date = new Date(item.createdAt ? item.createdAt : "").getTime();
      return {
        type: "comment",
        comment: item,
        date: date,
      };
    });
    const topicTime = new Date(
      topic?.createdAt ? topic?.createdAt : ""
    ).getTime();
    let firstHis = history.findIndex(
      (value) =>
        new Date(value.created_at ? value.created_at : "").getTime() > topicTime
    );
    if (firstHis < 0) {
      firstHis = history.length;
    }
    const hisFiltered = history.filter(
      (_value, index) => index >= firstHis - 1
    );
    const his: IItem[] = hisFiltered.map((item, index) => {
      return {
        type: "sent",
        sent: [item],
        date: new Date(item.created_at ? item.created_at : "").getTime(),
        oldSent: index > 0 ? hisFiltered[index - 1].content : undefined,
      };
    });
    const mixItems = [...comment, ...his];
    mixItems.sort((a, b) => a.date - b.date);
    console.log("mixItems", mixItems);
    const newMixItems: IItem[] = [];
    let currSent: ISentHistoryData[] = [];
    let currOldSent: string | undefined;
    let sentBegin = false;
    mixItems.forEach((value) => {
      if (value.type === "comment") {
        if (sentBegin) {
          sentBegin = false;
          newMixItems.push({
            type: "sent",
            sent: currSent,
            date: 0,
            oldSent: currOldSent ? currOldSent : currSent[0].content,
          });
        }
        newMixItems.push(value);
      } else {
        if (value.sent && value.sent.length > 0) {
          if (sentBegin) {
            currSent.push(value.sent[0]);
          } else {
            sentBegin = true;
            currSent = value.sent;
            currOldSent = value.oldSent;
          }
        }
      }
    });
    if (sentBegin) {
      sentBegin = false;
      newMixItems.push({
        type: "sent",
        sent: currSent,
        date: 0,
        oldSent: currOldSent ? currOldSent : currSent[0].content,
      });
    }
    setItems(newMixItems);
  }, [data, history, topic?.createdAt]);

  useEffect(() => {
    if (resType === "sentence" && resId) {
      const url = `/api/v2/sent_history?view=sentence&id=${resId}&order=created_at&dir=asc`;
      setLoading(true);
      get<ISentHistoryListResponse>(url)
        .then((res) => {
          if (res.ok) {
            setHistory(res.data.rows);
          }
        })
        .finally(() => setLoading(false));
    }
  }, [resId, resType]);

  useEffect(() => {
    console.log("topicId", topicId);
    if (typeof topicId === "undefined") {
      return;
    }
    setLoading(true);
    fetchReplies(topicId)
      .then(setData)
      .finally(() => setLoading(false));
  }, [topicId]);
  return (
    <div>
      {loading ? (
        <Skeleton title={{ width: 200 }} paragraph={{ rows: 1 }} active />
      ) : (
        <List
          pagination={false}
          size="small"
          itemLayout="horizontal"
          dataSource={items}
          renderItem={(item) => {
            return (
              <List.Item>
                {item.type === "comment" ? (
                  item.comment ? (
                    <DiscussionItem
                      data={item.comment}
                      isFocus={item.comment.id === focus ? true : false}
                      onDelete={() => {
                        if (typeof onItemCountChange !== "undefined") {
                          onItemCountChange(
                            data.length - 1,
                            item.comment?.parent
                          );
                        }
                        setData((origin) => {
                          return origin.filter(
                            (value) => value.id !== item.comment?.id
                          );
                        });
                      }}
                    />
                  ) : undefined
                ) : (
                  <SentHistoryGroup
                    data={item.sent}
                    oldContent={item.oldSent}
                  />
                )}
              </List.Item>
            );
          }}
        />
      )}
      {hideReply ||
      // 话题关闭、删除或没有回复权限时不给回复框；尚未创建的模板话题由回复框负责创建
      (topic && !topic.newTpl && !topic.abilities.reply) ? (
        <></>
      ) : (
        <DiscussionCreate
          resId={resId}
          resType={resType}
          parent={topicId}
          topic={topic}
          onCreated={(value: IComment) => {
            const newData = JSON.parse(JSON.stringify(value));
            setData([...data, newData]);
            if (typeof onItemCountChange !== "undefined") {
              onItemCountChange(data.length + 1, value.parent);
            }
          }}
          onTopicCreated={(value) => {
            if (typeof onTopicCreate !== "undefined") {
              onTopicCreate(value);
            }
          }}
        />
      )}
    </div>
  );
};

export default DiscussionTopicChildrenWidget;
