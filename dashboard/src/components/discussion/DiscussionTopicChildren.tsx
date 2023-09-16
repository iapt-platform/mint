import { List, message, Skeleton } from "antd";
import { useEffect, useState } from "react";
import { useIntl } from "react-intl";

import { get } from "../../request";
import { ICommentListResponse } from "../api/Comment";
import {
  ISentHistoryData,
  ISentHistoryListResponse,
} from "../corpus/SentHistory";
import SentHistoryGroup from "../corpus/SentHistoryGroup";
import SentHistoryItemWidget from "../corpus/SentHistoryItem";
import DiscussionCreate from "./DiscussionCreate";
import DiscussionItem, { IComment } from "./DiscussionItem";
import { TResType } from "./DiscussionListCard";

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
  onItemCountChange?: Function;
}
const DiscussionTopicChildrenWidget = ({
  topic,
  resId,
  resType,
  topicId,
  focus,
  onItemCountChange,
}: IWidget) => {
  const intl = useIntl();
  const [data, setData] = useState<IComment[]>([]);
  const [loading, setLoading] = useState(true);
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
    const firstHis = history.findIndex(
      (value) =>
        new Date(value.created_at ? value.created_at : "").getTime() > topicTime
    );
    const hisFiltered = history.filter((value, index) => index >= firstHis - 1);
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
    let newMixItems: IItem[] = [];
    let currSent: ISentHistoryData[] = [];
    let currOldSent: string | undefined;
    let sentBegin = false;
    mixItems.forEach((value, index, array) => {
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
      let url = `/v2/sent_history?view=sentence&id=${resId}&order=created_at&dir=asc`;
      get<ISentHistoryListResponse>(url).then((res) => {
        if (res.ok) {
          setHistory(res.data.rows);
        }
      });
    }
  }, [resId, resType]);

  useEffect(() => {
    if (typeof topicId === "undefined") {
      return;
    }
    setLoading(true);

    get<ICommentListResponse>(`/v2/discussion?view=answer&id=${topicId}`)
      .then((json) => {
        if (json.ok) {
          const discussions: IComment[] = json.data.rows.map((item) => {
            return {
              id: item.id,
              resId: item.res_id,
              resType: item.res_type,
              user: item.editor,
              parent: item.parent,
              title: item.title,
              content: item.content,
              status: item.status,
              childrenCount: item.children_count,
              createdAt: item.created_at,
              updatedAt: item.updated_at,
            };
          });
          setData(discussions);
        } else {
          message.error(json.message);
        }
      })
      .finally(() => {
        setLoading(false);
      })
      .catch((e) => {
        message.error(e.message);
      });
  }, [intl, topicId]);
  return (
    <div>
      {loading ? (
        <Skeleton title={{ width: 200 }} paragraph={{ rows: 1 }} active />
      ) : (
        <List
          pagination={false}
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
      <DiscussionCreate
        contentType="markdown"
        parent={topicId}
        onCreated={(e: IComment) => {
          const newData = JSON.parse(JSON.stringify(e));
          setData([...data, newData]);
          if (typeof onItemCountChange !== "undefined") {
            onItemCountChange(data.length + 1, e.parent);
          }
        }}
      />
    </div>
  );
};

export default DiscussionTopicChildrenWidget;
