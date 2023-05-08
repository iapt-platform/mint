import { useState, useEffect } from "react";
import { useIntl } from "react-intl";
import { Card, message } from "antd";

import { get } from "../../request";
import { ICommentListResponse } from "../api/Comment";
import CommentCreate from "./CommentCreate";
import { IComment } from "./CommentItem";
import CommentList from "./CommentList";
import { IAnswerCount } from "./CommentBox";

export type TResType = "article" | "channel" | "chapter" | "sentence" | "wbw";
interface IWidget {
  resId?: string;
  resType?: TResType;
  topicId?: string;
  changedAnswerCount?: IAnswerCount;
  onSelect?: Function;
  onItemCountChange?: Function;
}
const Widget = ({
  resId,
  resType,
  topicId,
  onSelect,
  changedAnswerCount,
  onItemCountChange,
}: IWidget) => {
  const intl = useIntl();
  const [data, setData] = useState<IComment[]>([]);
  useEffect(() => {
    console.log("changedAnswerCount", changedAnswerCount);
    const newData = [...data].map((item) => {
      const newItem = item;
      if (newItem.id && changedAnswerCount?.id === newItem.id) {
        newItem.childrenCount = changedAnswerCount.count;
      }
      return newItem;
    });
    setData(newData);
  }, [changedAnswerCount]);

  useEffect(() => {
    let url: string = "";
    if (typeof topicId !== "undefined") {
      url = `/v2/discussion?view=question-by-topic&id=${topicId}`;
    }
    if (typeof resId !== "undefined") {
      url = `/v2/discussion?view=question&id=${resId}`;
    }
    if (url === "") {
      return;
    }
    get<ICommentListResponse>(url)
      .then((json) => {
        console.log(json);
        if (json.ok) {
          console.log(intl.formatMessage({ id: "flashes.success" }));
          const discussions: IComment[] = json.data.rows.map((item) => {
            return {
              id: item.id,
              resId: item.res_id,
              resType: item.res_type,
              user: item.editor,
              title: item.title,
              content: item.content,
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
      .catch((e) => {
        message.error(e.message);
      });
  }, [intl, resId, topicId]);

  if (typeof resId === "undefined" && typeof topicId === "undefined") {
    return <div>该资源尚未创建，不能发表讨论。</div>;
  }

  return (
    <div>
      <Card title="讨论" extra={"More"}>
        {data.length > 0 ? (
          <CommentList
            onSelect={(
              e: React.MouseEvent<HTMLSpanElement, MouseEvent>,
              comment: IComment
            ) => {
              if (typeof onSelect !== "undefined") {
                onSelect(e, comment);
              }
            }}
            data={data}
          />
        ) : undefined}

        {resId && resType ? (
          <CommentCreate
            contentType="markdown"
            resId={resId}
            resType={resType}
            onCreated={(e: IComment) => {
              const newData = JSON.parse(JSON.stringify(e));
              console.log("create", e);
              if (typeof onItemCountChange !== "undefined") {
                onItemCountChange(data.length + 1);
              }
              setData([...data, newData]);
            }}
          />
        ) : undefined}
      </Card>
    </div>
  );
};

export default Widget;
