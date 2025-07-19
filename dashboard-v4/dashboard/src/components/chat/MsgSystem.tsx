import { useState } from "react";

interface IWidget {
  value?: string;
}
const MsgSystem = ({ value }: IWidget) => {
  const [display, setDisplay] = useState(false);
  return (
    <div
      style={{
        backgroundColor: "#fafafa",
        border: "1px dashed #d9d9d9",
        borderRadius: 4,
        marginTop: 8,
        fontSize: 12,
        color: "#888",
        padding: 8,
      }}
    >
      <div
        style={{ cursor: "pointer", userSelect: "none" }}
        onClick={() => {
          setDisplay(!display);
        }}
      >
        {display ? "▼ 收起资料" : "▶ 展开资料"}
      </div>
      <div style={{ display: display ? "block" : "none" }}>
        <pre
          style={{
            whiteSpace: "pre-wrap",
            wordBreak: "break-word",
            marginTop: 4,
          }}
        >
          {value}
        </pre>
      </div>
    </div>
  );
};
export default MsgSystem;
