import { useEffect, useState } from "react";
import { useAppSelector } from "../../hooks";
import { currFocus } from "../../reducers/focus";
import { ParaHandleCtl } from "./ParaHandle";
import AiTranslate from "../ai/AiTranslate";

interface IWidgetParaShellCtl {
  book: number;
  para: number;
  mode?: string;
  channels?: string[];
  sentences: string[];
  children?: React.ReactNode | React.ReactNode[];
}
const ParaShellCtl = ({
  book,
  para,
  mode = "read",
  channels,
  sentences,
  children,
}: IWidgetParaShellCtl) => {
  const focus = useAppSelector(currFocus);
  const [isFocus, setIsFocus] = useState(false);
  const [aiTranslateParaId, setAiTranslateParaId] = useState<string>();
  useEffect(() => {
    if (focus) {
      if (focus.focus?.type === "para") {
        if (focus.focus.id) {
          const arrId = focus.focus.id.split("-");
          if (arrId.length > 1) {
            const focusBook = parseInt(arrId[0]);
            const focusPara = arrId[1].split(",").map((item) => parseInt(item));
            if (focusBook === book && focusPara.includes(para)) {
              setIsFocus(true);
            }
          }
        } else {
          setIsFocus(false);
        }
      }
    } else {
      setIsFocus(false);
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
          position: "absolute",
          marginTop: -31,
          marginLeft: -6,
          border: border,
          borderRadius: "6px",
        }}
      >
        <ParaHandleCtl
          book={book}
          para={para}
          mode={mode}
          channels={channels}
          sentences={sentences}
          onTranslate={() => setAiTranslateParaId(`${book}-${para}`)}
        />
      </div>
      <div>
        <AiTranslate autoLoad paragraph={aiTranslateParaId} />
      </div>
      {children}
    </div>
  );
};

interface IWidget {
  props: string;
  children?: React.ReactNode | React.ReactNode[];
}
const Widget = ({ props, children }: IWidget) => {
  const prop = JSON.parse(atob(props)) as IWidgetParaShellCtl;
  return (
    <>
      <ParaShellCtl {...prop}>{children}</ParaShellCtl>
    </>
  );
};

export default Widget;
