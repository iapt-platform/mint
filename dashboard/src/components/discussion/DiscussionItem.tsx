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
  children?: IComment[];
  childrenCount?: number;
  createdAt?: string;
  updatedAt?: string;
}
interface IWidget {
  data: IComment;
  onSelect?: Function;
  onCreated?: Function;
  onDelete?: Function;
}
const DiscussionItemWidget = ({
  data,
  onSelect,
  onCreated,
  onDelete,
}: IWidget) => {
  const [edit, setEdit] = useState(false);
  const [currData, setCurrData] = useState<IComment>(data);
  return (
    <div style={{ display: "flex", width: "100%" }}>
      <div style={{ width: "2em" }}>
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
            onSelect={(
              e: React.MouseEvent<HTMLSpanElement, MouseEvent>,
              data: IComment
            ) => {
              if (typeof onSelect !== "undefined") {
                onSelect(e, data);
              }
            }}
            onDelete={(id: string) => {
              if (typeof onDelete !== "undefined") {
                onDelete();
              }
            }}
          />
        )}
      </div>
    </div>
  );
};

export default DiscussionItemWidget;
