import { useMemo, useState } from "react";
import { EditOutlined, EyeOutlined } from "@ant-design/icons";

import { useAppSelector } from "../../hooks";
import { currFocus } from "../../reducers/focus";
import { ParaHandleCtl } from "./ParaHandle";
import { SentEditInner, type IWidgetSentEditInner } from "../sentence/SentEdit";
import { Button } from "antd";
import type { ArticleMode } from "../../api/article";
import ParagraphRead from "../tipitaka/components/ParagraphRead";

export interface IParagraphProps {
  book: number;
  para: number;
  mode?: ArticleMode;
  channels?: string[];
  sentenceIds: string[];
  children?: IWidgetSentEditInner[];
  onModeChange?: (mode: ArticleMode) => void;
}
export const ParagraphCtl = ({
  book,
  para,
  mode = "read",
  channels,
  sentenceIds,
  children,
  onModeChange,
}: IParagraphProps) => {
  const [innerMode, setInnerMode] = useState<ArticleMode>("read");
  const focus = useAppSelector(currFocus);
  console.debug("para children", book, para, children?.length);
  console.debug("para children", children);
  const isFocus = useMemo(() => {
    if (focus) {
      if (focus.focus?.type === "para") {
        if (focus.focus.id) {
          const arrId = focus.focus.id.split("-");
          if (arrId.length > 1) {
            const focusBook = parseInt(arrId[0]);
            const focusPara = arrId[1].split(",").map((item) => parseInt(item));
            if (focusBook === book && focusPara.includes(para)) {
              return true;
            }
          }
        } else {
          return false;
        }
      }
    } else {
      return false;
    }
  }, [book, focus, para]);

  const borderColor = isFocus ? "#e35f00bd " : "rgba(128, 128, 128, 0.3)";

  const border = mode === "read" ? "" : "2px solid " + borderColor;

  return (
    <div
      style={{
        border: border,
        borderRadius: 6,
        marginTop: 20,
        marginBottom: 28,
        padding: 4,
      }}
    >
      <div
        style={{
          display: "flex",
          justifyContent: "space-between",
        }}
      >
        <ParaHandleCtl
          book={book}
          para={para}
          mode={mode}
          channels={channels}
          sentences={sentenceIds}
        />
        <div>
          {innerMode === "edit" && (
            <Button
              type="link"
              icon={<EyeOutlined />}
              onClick={() => {
                if (onModeChange) {
                  onModeChange("read");
                } else {
                  setInnerMode("read");
                }
              }}
            />
          )}
          {innerMode === "read" && (
            <Button
              type="link"
              icon={<EditOutlined />}
              onClick={() => {
                if (onModeChange) {
                  onModeChange("edit");
                } else {
                  setInnerMode("edit");
                }
              }}
            />
          )}
        </div>
      </div>
      <div>
        {innerMode === "edit" &&
          children?.map((item) => <SentEditInner {...item} />)}
        {innerMode === "read" && <ParagraphRead data={children} />}
      </div>
    </div>
  );
};

interface IWidget {
  props: string;
  children?: React.ReactNode | React.ReactNode[];
}
const Widget = ({ props }: IWidget) => {
  const prop = JSON.parse(atob(props)) as IParagraphProps;
  return (
    <>
      <ParagraphCtl {...prop} />
    </>
  );
};

export default Widget;
