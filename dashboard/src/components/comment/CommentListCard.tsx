import { Card, message } from "antd";
import { useState, useEffect } from "react";
import { useIntl } from "react-intl";
import { useAppSelector } from "../../hooks";
import { currentUser as _currentUser } from "../../reducers/current-user";
import { get } from "../../request";
import { ICommentListResponse } from "../api/Comment";
import CommentCreate from "./CommentCreate";

import { IComment } from "./CommentItem";
import CommentList from "./CommentList";

interface IWidget {
  resId?: string;
  resType?: string;
  onSelect?: Function;
  onItemCountChange?: Function;
}
const Widget = ({ resId, resType, onSelect, onItemCountChange }: IWidget) => {
  const intl = useIntl();
  const [data, setData] = useState<IComment[]>([]);

  const _currUser = useAppSelector(_currentUser);
  useEffect(() => {
    get<ICommentListResponse>(`/v2/discussion?view=res_id&id=${resId}`)
      .then((json) => {
        console.log(json);
        if (json.ok) {
          message.success(intl.formatMessage({ id: "flashes.success" }));
          const discussions: IComment[] = json.data.rows.map((item) => {
            return {
              id: item.id,
              resId: item.res_id,
              resType: item.res_type,
              user: {
                id: item.editor?.id ? item.editor.id : "",
                nickName: item.editor?.nickName ? item.editor.nickName : "",
                realName: item.editor?.userName ? item.editor.userName : "",
                avatar: item.editor?.avatar ? item.editor.avatar : "",
              },
              title: item.title,
              content: item.content,
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
  }, [intl]);

  if (typeof resId === "undefined") {
    return <div>该资源尚未创建，不能发表讨论。</div>;
  }

  return (
    <div>
      <Card title="问题列表" extra={<a href="#">More</a>}>
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
        {
          <CommentCreate
            data={{
              resId: resId,
              resType: resType,
              user: {
                id: "string",
                nickName: _currUser ? _currUser.nickName : "Visuddhinanda",
                realName: _currUser ? _currUser.realName : "Visuddhinanda",
                avatar: _currUser ? _currUser.avatar : "",
              },
            }}
            onCreated={(e: IComment) => {
              // const orgData = JSON.parse(JSON.stringify(data));
              if (typeof onItemCountChange !== "undefined") {
                onItemCountChange(data.length + 1);
              }
              setData([...data, e]);
            }}
          />
        }
      </Card>
    </div>
  );
};

export default Widget;
