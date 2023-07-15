import { Avatar } from "antd";
import { useState } from "react";
import { IUser } from "../auth/User";
import CommentShow from "./DiscussionShow";
import CommentEdit from "./DiscussionEdit";
import { TResType } from "./DiscussionListCard";

export interface IComment {
  id?: string; //id未提供为新建
  resId?: string;
  resType?: TResType;
  user: IUser;
  parent?: string;
  title?: string;
  content?: string;
  children?: IComment[];
  childrenCount?: number;
  createdAt?: string;
  updatedAt?: string;
}
interface IWidget {
  data: IComment;
  onSelect?: Function;
  onCreated?: Function;
}
const DiscussionItemWidget = ({ data, onSelect, onCreated }: IWidget) => {
  const [edit, setEdit] = useState(false);
  return (
    <div style={{ display: "flex" }}>
      <div style={{ width: "2em" }}>
        <Avatar size="small">{data.user?.nickName?.slice(0, 1)}</Avatar>
      </div>
      <div style={{ width: "100%" }}>
        {edit ? (
          <CommentEdit
            data={data}
            onCreated={(e: IComment) => {
              if (typeof onCreated !== "undefined") {
                onCreated(e);
              }
            }}
          />
        ) : (
          <CommentShow
            data={data}
            onEdit={() => {
              setEdit(true);
            }}
          />
        )}
      </div>
    </div>
  );
};

export default DiscussionItemWidget;
