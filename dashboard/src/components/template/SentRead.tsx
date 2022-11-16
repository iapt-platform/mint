import { Tooltip, Button } from "antd";
import MdView from "./MdView";
import { ISentence } from "./SentEdit";

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
      <div style={{ display: "flex", flexDirection: layout }}>
        <div style={{ flex: "5", color: "#9f3a01" }}>
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
