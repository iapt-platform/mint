import { useMemo } from "react";
import { EditOutlined, EyeOutlined } from "@ant-design/icons";

import { useAppSelector } from "../../hooks";
import { currFocus } from "../../reducers/focus";
import { ParaHandleCtl } from "./ParaHandle";
import { SentEditInner } from "../sentence/SentEdit";
import { Button } from "antd";
import type { ArticleMode } from "../../api/article";
import ParagraphRead from "../tipitaka/components/ParagraphRead";
import type { IParagraphNode } from "../../api/pali-text";

interface IParagraphProps extends IParagraphNode {
  loading?: boolean;
  onModeChange?: (mode: ArticleMode) => void;
}

export const ParagraphCtl = ({
  book,
  para,
  mode,
  channels,
  sentenceIds,
  children,
  loading,
  onModeChange,
}: IParagraphProps) => {
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
          {mode === "edit" && (
            <Button
              loading={loading}
              type="link"
              icon={<EyeOutlined />}
              onClick={() => {
                if (onModeChange) {
                  onModeChange("read");
                }
              }}
            />
          )}
          {mode === "read" && (
            <Button
              loading={loading}
              type="link"
              icon={<EditOutlined />}
              onClick={() => {
                if (onModeChange) {
                  onModeChange("edit");
                }
              }}
            />
          )}
        </div>
      </div>
      <div>
        {mode === "edit" &&
          children?.map((item) => <SentEditInner {...item} />)}
        {mode === "read" && <ParagraphRead data={children} />}
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
