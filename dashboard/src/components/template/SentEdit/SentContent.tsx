import { ISentence } from "../SentEdit";
import SentCell from "./SentCell";
import { WbwSentCtl } from "../WbwSent";
import { useAppSelector } from "../../../hooks";
import { settingInfo } from "../../../reducers/setting";
import { useEffect, useState } from "react";
import { GetUserSetting } from "../../auth/setting/default";
import { mode } from "../../../reducers/article-mode";
import { IWbw } from "../Wbw/WbwWord";

interface ILayoutFlex {
  left: number;
  right: number;
}
type TDirection = "row" | "column";
interface IWidgetSentContent {
  sid?: string;
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
  origin?: ISentence[];
  translation?: ISentence[];
  layout?: TDirection;
  magicDict?: string;
  onWbwChange?: Function;
}
const Widget = ({
  sid,
  book,
  para,
  wordStart,
  wordEnd,
  origin,
  translation,
  layout = "column",
  magicDict,
  onWbwChange,
}: IWidgetSentContent) => {
  const [layoutDirection, setLayoutDirection] = useState<TDirection>(layout);
  const [layoutFlex, setLayoutFlex] = useState<ILayoutFlex>({
    left: 5,
    right: 5,
  });
  const settings = useAppSelector(settingInfo);
  useEffect(() => {
    const layoutDirection = GetUserSetting(
      "setting.layout.direction",
      settings
    );
    if (typeof layoutDirection === "string") {
      setLayoutDirection(layoutDirection as TDirection);
    }
  }, [settings]);

  const newMode = useAppSelector(mode);
  useEffect(() => {
    switch (newMode) {
      case "edit":
        setLayoutFlex({
          left: 5,
          right: 5,
        });
        break;
      case "wbw":
        setLayoutFlex({
          left: 7,
          right: 3,
        });
        break;
    }
  }, [newMode]);
  return (
    <div
      style={{
        display: "flex",
        flexDirection: layoutDirection,
        marginBottom: 10,
      }}
    >
      <div
        dangerouslySetInnerHTML={{
          __html: `<div class="pcd_sent" id="sent_${sid}"></div>`,
        }}
      />
      <div style={{ flex: layoutFlex.left, color: "#9f3a01" }}>
        {origin?.map((item, id) => {
          if (item.channel.type === "wbw") {
            return (
              <WbwSentCtl
                key={id}
                book={book}
                para={para}
                wordStart={wordStart}
                wordEnd={wordEnd}
                magicDict={magicDict}
                channelId={item.channel.id}
                data={JSON.parse(item.content)}
                onChange={(data: IWbw[]) => {
                  if (typeof onWbwChange !== "undefined") {
                    onWbwChange(data);
                  }
                }}
              />
            );
          } else {
            return <SentCell key={id} data={item} wordWidget={true} />;
          }
        })}
      </div>
      <div style={{ flex: layoutFlex.right }}>
        {translation?.map((item, id) => {
          return <SentCell key={id} data={item} />;
        })}
      </div>
    </div>
  );
};

export default Widget;
