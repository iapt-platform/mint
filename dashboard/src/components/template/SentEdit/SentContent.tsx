import { ISentence } from "../SentEdit";
import SentCell from "./SentCell";
import { WbwSentCtl } from "../WbwSent";
interface IWidgetSentContent {
  origin?: ISentence[];
  translation?: ISentence[];
  layout?: "row" | "column";
}
const Widget = ({
  origin,
  translation,
  layout = "column",
}: IWidgetSentContent) => {
  return (
    <div style={{ display: "flex", flexDirection: layout }}>
      <div style={{ flex: "5", color: "#9f3a01" }}>
        {origin?.map((item, id) => {
          if (item.channel.type === "wbw") {
            return <WbwSentCtl key={id} data={JSON.parse(item.content)} />;
          } else {
            return <SentCell key={id} data={item} wordWidget={true} />;
          }
        })}
      </div>
      <div style={{ flex: "5" }}>
        {translation?.map((item, id) => {
          return <SentCell key={id} data={item} />;
        })}
      </div>
    </div>
  );
};

export default Widget;
