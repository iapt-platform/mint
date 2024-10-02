import { Avatar } from "antd";
import { useEffect, useState } from "react";
import { IUser } from "../auth/User";
import DiscussionShow from "./DiscussionShow";
import DiscussionEdit from "./DiscussionEdit";
import { TResType } from "./DiscussionListCard";
import { TDiscussionType } from "./Discussion";

export interface IComment {
  id?: string; //id未提供为新建
  resId?: string;
  resType?: TResType;
  type: TDiscussionType;
  tplId?: string;
  user: IUser;
  parent?: string | null;
  title?: string;
  content?: string;
  html?: string;
  summary?: string;
  status?: "active" | "close";
  children?: IComment[];
  childrenCount?: number;
  newTpl?: boolean;
  createdAt?: string;
  updatedAt?: string;
}
interface IWidget {
  data: IComment;
  isFocus?: boolean;
  hideTitle?: boolean;
  onSelect?: Function;
  onCreated?: Function;
  onDelete?: Function;
  onReply?: Function;
  onClose?: Function;
  onConvert?: Function;
}
const DiscussionItemWidget = ({
  data,
  isFocus = false,
  hideTitle = false,
  onSelect,
  onCreated,
  onDelete,
  onReply,
  onClose,
  onConvert,
}: IWidget) => {
  const [edit, setEdit] = useState(false);
  const [currData, setCurrData] = useState<IComment>(data);
  useEffect(() => {
    setCurrData(data);
  }, [data]);
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
            hideTitle={hideTitle}
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
            onClose={(value: boolean) => {
              if (typeof onClose !== "undefined") {
                onClose(value);
              }
            }}
            onConvert={(value: TDiscussionType) => {
              if (typeof onConvert !== "undefined") {
                onConvert(value);
              }
            }}
          />
        )}
      </div>
    </div>
  );
};

export default DiscussionItemWidget;
