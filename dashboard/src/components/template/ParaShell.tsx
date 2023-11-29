import { useEffect, useState } from "react";
import { useAppSelector } from "../../hooks";
import { currFocus } from "../../reducers/focus";
import { ParaHandleCtl } from "./ParaHandle";

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
  return (
    <div
      style={{
        border: isFocus ? "2px solid #e35f00bd " : "1px solid #006bffc7",
        borderRadius: 6,
        marginTop: 20,
        paddingTop: 16,
      }}
    >
      <div
        style={{
          position: "absolute",
          marginTop: "-2em",
          marginLeft: "1em",
          backgroundColor: "#d9e9ff",
          borderRadius: "4px",
        }}
      >
        <ParaHandleCtl
          book={book}
          para={para}
          mode={mode}
          channels={channels}
          sentences={sentences}
        />
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
