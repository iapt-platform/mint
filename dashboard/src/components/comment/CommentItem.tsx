import { Avatar } from "antd";
import { useState } from "react";
import { IUser } from "../auth/User";
import CommentShow from "./CommentShow";
import CommentEdit from "./CommentEdit";

export interface IComment {
  id?: string; //id未提供为新建
  resId?: string;
  resType?: string;
  user: IUser;
  parent?: string;
  title?: string;
  content?: string;
  children?: IComment[];
  createdAt?: string;
  updatedAt?: string;
}
interface IWidget {
  data: IComment;
  create?: boolean;
  onSelect?: Function;
  onCreated?: Function;
}
const Widget = ({ data, create = false, onSelect, onCreated }: IWidget) => {
  const [edit, setEdit] = useState(false);

  return (
    <div style={{ display: "flex" }}>
      <div style={{ width: "auto", padding: 8 }}>
        <Avatar>{data.user.nickName.slice(0, 1)}</Avatar>
      </div>
      <div style={{ flex: "auto" }}>
        {edit || create ? (
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

export default Widget;
