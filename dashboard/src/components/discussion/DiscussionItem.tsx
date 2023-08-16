import { Avatar } from "antd";
import { useState } from "react";
import { IUser } from "../auth/User";
import DiscussionShow from "./DiscussionShow";
import DiscussionEdit from "./DiscussionEdit";
import { TResType } from "./DiscussionListCard";

export interface IComment {
  id?: string; //id未提供为新建
  resId?: string;
  resType?: TResType;
  user: IUser;
  parent?: string | null;
  title?: string;
  content?: string;
  status?: "active" | "close";
  children?: IComment[];
  childrenCount?: number;
  createdAt?: string;
  updatedAt?: string;
}
interface IWidget {
  data: IComment;
  isFocus?: boolean;
  onSelect?: Function;
  onCreated?: Function;
  onDelete?: Function;
  onReply?: Function;
  onClose?: Function;
}
const DiscussionItemWidget = ({
  data,
  isFocus = false,
  onSelect,
  onCreated,
  onDelete,
  onReply,
  onClose,
}: IWidget) => {
  const [edit, setEdit] = useState(false);
  const [currData, setCurrData] = useState<IComment>(data);
  return (
    <div
      id={`answer-${data.id}`}
      style={{
        display: "flex",
        width: "100%",
        border: isFocus ? "2px solid blue" : "unset",
        borderRadius: 10,
        padding: 5,
      }}
    >
      <div style={{ width: "2em", display: "none" }}>
        <Avatar size="small">{data.user?.nickName?.slice(0, 1)}</Avatar>
      </div>
      <div style={{ width: "100%" }}>
        {edit ? (
          <DiscussionEdit
            data={currData}
            onUpdated={(e: IComment) => {
              setCurrData(e);
              setEdit(false);
            }}
            onCreated={(e: IComment) => {
              if (typeof onCreated !== "undefined") {
                onCreated(e);
              }
            }}
            onClose={() => setEdit(false)}
          />
        ) : (
          <DiscussionShow
            data={currData}
            onEdit={() => {
              setEdit(true);
            }}
            onSelect={(e: React.MouseEvent<HTMLSpanElement, MouseEvent>) => {
              if (typeof onSelect !== "undefined") {
                onSelect(e, currData);
              }
            }}
            onDelete={(id: string) => {
              if (typeof onDelete !== "undefined") {
                onDelete();
              }
            }}
            onReply={() => {
              if (typeof onReply !== "undefined") {
                onReply(currData);
              }
            }}
            onClose={() => {
              if (typeof onClose !== "undefined") {
                onClose();
              }
            }}
          />
        )}
      </div>
    </div>
  );
};

export default DiscussionItemWidget;
