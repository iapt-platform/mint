import { useEffect, useState } from "react";
import { TResType } from "./DiscussionListCard";
import { get } from "../../request";
import { ICommentListResponse } from "../api/Comment";
import DiscussionItem, { IComment } from "./DiscussionItem";

interface IWidget {
  resId?: string;
  resType?: TResType;
  onSelect?: Function;
  onReply?: Function;
}
const QaListWidget = ({ resId, resType, onSelect, onReply }: IWidget) => {
  const [data, setData] = useState<IComment[]>();

  useEffect(() => {
    if (!resType || !resType) {
      return;
    }
    let url: string = `/v2/discussion?res_type=${resType}&view=res_id&id=${resId}`;
    url += "&dir=asc&type=qa&status=active,close";
    console.info("api request", url);
    get<ICommentListResponse>(url).then((json) => {
      if (json.ok) {
        console.debug("discussion api response", json);
        const items: IComment[] = json.data.rows.map((item, id) => {
          return {
            id: item.id,
            resId: item.res_id,
            resType: item.res_type,
            type: item.type,
            user: item.editor,
            title: item.title,
            parent: item.parent,
            tplId: item.tpl_id,
            content: item.content,
            summary: item.summary,
            status: item.status,
            childrenCount: item.children_count,
            createdAt: item.created_at,
            updatedAt: item.updated_at,
          };
        });

        setData(items);
      }
    });
  }, []);
  return (
    <>
      {data
        ?.filter((value) => !value.parent)
        .map((question, index) => {
          return (
            <div key={`div_${index}`}>
              <DiscussionItem
                key={index}
                data={question}
                onSelect={(
                  e: React.MouseEvent<HTMLSpanElement, MouseEvent>,
                  value: IComment
                ) => {
                  if (typeof onSelect !== "undefined") {
                    onSelect(e, value);
                  }
                }}
              />
              <div
                style={{
                  marginLeft: 16,
                  borderLeft: "2px solid gray",
                  padding: 4,
                }}
              >
                {data
                  ?.filter((value) => value.parent === question.id)
                  .map((item, id) => {
                    return <DiscussionItem key={id} data={item} />;
                  })}
              </div>
            </div>
          );
        })}
    </>
  );
};

export default QaListWidget;
