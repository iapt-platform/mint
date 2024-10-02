import { ISentence } from "../SentEdit";
import SentCell from "./SentCell";
import { WbwSentCtl } from "../WbwSent";
import { useAppSelector } from "../../../hooks";
import { settingInfo } from "../../../reducers/setting";
import { useEffect, useLayoutEffect, useRef, useState } from "react";
import { GetUserSetting } from "../../auth/setting/default";
import { mode as _mode } from "../../../reducers/article-mode";
import { IWbw } from "../Wbw/WbwWord";
import { ArticleMode } from "../../article/Article";
import SuggestionFocus from "./SuggestionFocus";
import store from "../../../store";
import { push } from "../../../reducers/sentence";

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
  answer?: ISentence;
  layout?: TDirection;
  magicDict?: string;
  compact?: boolean;
  mode?: ArticleMode;
  wbwProgress?: boolean;
  readonly?: boolean;
  onWbwChange?: Function;
  onTranslationChange?: (data: ISentence) => void;
  onMagicDictDone?: Function;
}
const SentContentWidget = ({
  sid,
  book,
  para,
  wordStart,
  wordEnd,
  origin,
  translation,
  answer,
  layout = "column",
  compact = false,
  mode,
  magicDict,
  wbwProgress = false,
  readonly = false,
  onWbwChange,
  onTranslationChange,
  onMagicDictDone,
}: IWidgetSentContent) => {
  const [layoutDirection, setLayoutDirection] = useState<TDirection>(layout);
  const [layoutFlex, setLayoutFlex] = useState<ILayoutFlex>({
    left: 5,
    right: 5,
  });
  const divShell = useRef<HTMLDivElement>(null);
  const settings = useAppSelector(settingInfo);
  const [divShellWidth, setDivShellWidth] = useState<number>();

  useEffect(() => {
    store.dispatch(
      push({
        id: `${book}-${para}-${wordStart}-${wordEnd}`,
        origin: origin?.map((item) => item.html),
        translation: translation?.map((item) => item.html),
      })
    );
  }, [book, origin, para, translation, wordEnd, wordStart]);

  useEffect(() => {
    const width = divShell.current?.offsetWidth;
    if (width && width < 550) {
      setLayoutDirection("column");
      return;
    }
    const layoutDirection = GetUserSetting(
      "setting.layout.direction",
      settings
    );

    if (typeof layoutDirection === "string") {
      setLayoutDirection(layoutDirection as TDirection);
    }
  }, [settings]);

  const newMode = useAppSelector(_mode);

  useEffect(() => {
    let currMode: ArticleMode | undefined;
    if (typeof mode !== "undefined") {
      currMode = mode;
    } else if (typeof newMode !== "undefined") {
      if (typeof newMode.id === "undefined") {
        currMode = newMode.mode;
      } else {
        const sentId = newMode.id.split("-");
        if (sentId.length === 2) {
          if (book === parseInt(sentId[0]) && para === parseInt(sentId[1])) {
            currMode = newMode.mode;
          }
        }
      }
    } else {
      return;
    }
    switch (currMode) {
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
  }, [book, mode, newMode, para]);

  useLayoutEffect(() => {
    const width = divShell.current?.offsetWidth;
    setDivShellWidth(width);
    if (width && width < 550) {
      setLayoutDirection("column");
      return;
    }
  }, []);

  return (
    <div
      ref={divShell}
      style={{
        display: "flex",
        flexDirection: layoutDirection,
        marginBottom: 0,
      }}
    >
      <div
        dangerouslySetInnerHTML={{
          __html: `<div class="pcd_sent" id="sent_${sid}"></div>`,
        }}
      />
      <div style={{ flex: layoutFlex.left, color: "#9f3a01" }}>
        {origin?.map((item, id) => {
          if (item.contentType === "json") {
            return (
              <WbwSentCtl
                key={id}
                book={book}
                para={para}
                wordStart={wordStart}
                wordEnd={wordEnd}
                studio={item.studio}
                channelId={item.channel.id}
                channelType={item.channel.type}
                channelLang={item.channel.lang}
                data={JSON.parse(item.content ?? "")}
                answer={answer ? JSON.parse(answer.content ?? "") : undefined}
                mode={mode}
                wbwProgress={wbwProgress}
                readonly={readonly}
                onChange={(data: IWbw[]) => {
                  if (typeof onWbwChange !== "undefined") {
                    onWbwChange(data);
                  }
                }}
                onMagicDictDone={() => {
                  if (typeof onMagicDictDone !== "undefined") {
                    onMagicDictDone();
                  }
                }}
              />
            );
          } else {
            return <SentCell key={id} initValue={item} wordWidget={true} />;
          }
        })}
      </div>
      <div style={{ flex: layoutFlex.right }}>
        {translation?.map((item, id) => {
          return (
            <SuggestionFocus
              key={id}
              book={item.book}
              para={item.para}
              start={item.wordStart}
              end={item.wordEnd}
              channelId={item.channel.id}
            >
              <SentCell
                key={id}
                initValue={item}
                compact={compact}
                onChange={onTranslationChange}
              />
            </SuggestionFocus>
          );
        })}
      </div>
    </div>
  );
};

export default SentContentWidget;
