import { Tooltip, Button } from "antd";
import MdView from "./MdView";
import { ISentence } from "./SentEdit";
import { useAppSelector } from "../../hooks";
import { onChangeKey, onChangeValue } from "../../reducers/setting";
import { useEffect, useRef } from "react";

interface IWidgetSentReadFrame {
  origin?: ISentence[];
  translation?: ISentence[];
  layout?: "row" | "column";
  sentId?: string;
}
const SentReadFrame = ({
  origin,
  translation,
  layout = "column",
  sentId,
}: IWidgetSentReadFrame) => {
  const key = useAppSelector(onChangeKey);
  const value = useAppSelector(onChangeValue);
  const boxOrg = useRef<HTMLDivElement>(null);
  const boxSent = useRef<HTMLDivElement>(null);

  useEffect(() => {
    switch (key) {
      case "setting.display.original":
        if (boxOrg.current) {
          if (value === true) {
            boxOrg.current.style.display = "block";
          } else {
            boxOrg.current.style.display = "none";
          }
        }
        break;
      case "setting.layout.direction":
        if (boxSent.current) {
          if (typeof value === "string") {
            boxSent.current.style.flexDirection = value;
          }
        }
        break;
      default:
        break;
    }
  }, [key, value]);
  return (
    <Tooltip
      placement="topLeft"
      color="white"
      title={
        <Button type="link" size="small">
          aa
        </Button>
      }
    >
      <div style={{ display: "flex", flexDirection: layout }} ref={boxSent}>
        <div style={{ flex: "5", color: "#9f3a01" }} ref={boxOrg}>
          {origin?.map((item, id) => {
            return <MdView key={id} html={item.html} />;
          })}
        </div>
        <div style={{ flex: "5" }}>
          {translation?.map((item, id) => {
            if (item.html.indexOf("<hr>") >= 0) console.log(item.html);
            return <MdView key={id} html={item.html} />;
          })}
        </div>
      </div>
    </Tooltip>
  );
};

interface IWidgetTerm {
  props: string;
}
const Widget = ({ props }: IWidgetTerm) => {
  const prop = JSON.parse(atob(props)) as IWidgetSentReadFrame;
  return (
    <>
      <SentReadFrame {...prop} />
    </>
  );
};

export default Widget;
