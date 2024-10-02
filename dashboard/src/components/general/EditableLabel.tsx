import { useEffect, useRef, useState } from "react";
import "./style.css";

interface IWidget {
  defaultValue?: string;
  value?: string;
  placeholder?: string;
  style?: React.CSSProperties;
  onChange?: Function;
  onKeyDown?: Function;
  onBlur?: Function;
  onPressEnter?: Function;
}
const EditableLabelWidget = ({
  defaultValue,
  value,
  placeholder,
  style,
  onChange,
  onKeyDown,
  onBlur,
  onPressEnter,
}: IWidget) => {
  const [textAreaValue, setTextAreaValue] = useState(defaultValue);
  const [shadowText, setShadowText] = useState(defaultValue);
  const [textAreaHeight, setTextAreaHeight] = useState<number | undefined>(31);
  const [shadowHeight, setShadowHeight] = useState<number | undefined>(31);

  const refTextArea = useRef<HTMLTextAreaElement>(null);
  const refShadow = useRef<HTMLDivElement>(null);
  useEffect(() => {
    setTextAreaValue(value);
    setShadowText(value);
  }, [value]);
  useEffect(() => {
    setTextAreaHeight(refShadow.current?.clientHeight);
  }, []);

  return (
    <div className="text_input" style={style}>
      <div
        ref={refShadow}
        className="textarea text_shadow"
        style={{
          height: shadowHeight,
          display: "inline-block",
          minHeight: "1em",
          minWidth: "15em",
        }}
        onResize={(event: React.SyntheticEvent<HTMLDivElement, Event>) => {
          setTextAreaHeight(refShadow.current?.clientHeight);
        }}
      >
        {shadowText}
      </div>
      <textarea
        className="textarea tran_sent_textarea"
        ref={refTextArea}
        style={{
          height: textAreaHeight,
          display: "inline-block",
          minHeight: "1em",
          minWidth: "15em",
          resize: "none",
          overflow: "hidden",
          borderBottom: "1px solid",
          borderRadius: 0,
        }}
        placeholder={placeholder}
        value={textAreaValue}
        onBlur={(event: React.FocusEvent<HTMLTextAreaElement, Element>) => {
          if (typeof onBlur !== "undefined") {
            onBlur(event);
          }
        }}
        onChange={(event: React.ChangeEvent<HTMLTextAreaElement>) => {
          setTextAreaValue(event.target.value);
          setShadowText(event.target.value);
          if (typeof onChange !== "undefined") {
            onChange(event);
          }
        }}
        onResize={(event) => {
          //setShadowHeight(refTextArea.current?.clientHeight);
        }}
        onKeyDown={(event: React.KeyboardEvent<HTMLTextAreaElement>) => {
          if (typeof onKeyDown !== "undefined") {
            onKeyDown(event);
          }
          if (event.key === "Enter" && typeof onPressEnter !== "undefined") {
            onPressEnter(event, textAreaValue);
          }
        }}
        onKeyUp={(event) => {
          //自适应高度
          if (
            refShadow.current === null ||
            refTextArea.current === null ||
            refTextArea.current.parentElement === null
          ) {
            return;
          }
          setTextAreaHeight(refShadow.current.scrollHeight);
        }}
      />
    </div>
  );
};

export default EditableLabelWidget;
