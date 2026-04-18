import { Avatar } from "antd";
import { useEffect, useState } from "react";

import DiscussionShow from "./DiscussionShow";
import DiscussionEdit from "./DiscussionEdit";
import type { IComment, TDiscussionType } from "../../api/discussion";

interface IWidget {
  data: IComment;
  isFocus?: boolean;
  hideTitle?: boolean;
  onSelect?: (
    e: React.MouseEvent<HTMLSpanElement, MouseEvent>,
    value: IComment
  ) => void;
  onCreated?: (e: IComment) => void;
  onDelete?: () => void;
  onClose?: (close: boolean) => void;
  onConvert?: (value: TDiscussionType) => void;
}
const DiscussionItemWidget = ({
  data,
  isFocus = false,
  hideTitle = false,
  onSelect,
  onDelete,
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
            onDelete={() => {
              if (typeof onDelete !== "undefined") {
                onDelete();
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
