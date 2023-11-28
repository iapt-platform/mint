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
  return (
    <div
      style={{
        borderTop: "1px solid #80808080",
        marginTop: 20,
        paddingTop: 16,
      }}
    >
      <div
        style={{
          position: "absolute",
          marginTop: "-2em",
          left: "56px",
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
