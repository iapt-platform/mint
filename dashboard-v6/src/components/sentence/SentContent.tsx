import SentCell from "./SentCell";
import { useAppSelector } from "../../hooks";
import { settingInfo } from "../../reducers/setting";
import { useEffect, useMemo, useRef, useState } from "react";

import { mode as _mode } from "../../reducers/article-mode";

import SuggestionFocus from "./SuggestionFocus";
import store from "../../store";
import { push } from "../../reducers/sentence";
import type { ISentence } from "../../api/sentence";
import type { ArticleMode } from "../../api/article";
import type { IWbw } from "../../types/wbw";
import { GetUserSetting } from "../setting/default";
import NissayaSent from "../nissaya/NissayaSent";
import WbwSentCtl from "../wbw/WbwSentCtl";

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
  onWbwChange?: (data: IWbw[]) => void;
  onTranslationChange?: (data: ISentence) => void;
  onMagicDictDone?: () => void;
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
  wbwProgress = false,
  readonly = false,
  onWbwChange,
  onTranslationChange,
  onMagicDictDone,
}: IWidgetSentContent) => {
  const divShell = useRef<HTMLDivElement>(null);
  const settings = useAppSelector(settingInfo);
  const newMode = useAppSelector(_mode);

  // Track container width via ResizeObserver — no setState inside effect
  const [containerWidth, setContainerWidth] = useState<number>(0);

  useEffect(() => {
    const el = divShell.current;
    if (!el) return;

    // Set initial width
    setContainerWidth(el.offsetWidth);

    const observer = new ResizeObserver((entries) => {
      const entry = entries[0];
      if (entry) {
        setContainerWidth(entry.contentRect.width);
      }
    });
    observer.observe(el);
    return () => observer.disconnect();
  }, []);

  // Derive layout direction from width + user setting — no setState in effect
  const layoutDirection = useMemo<TDirection>(() => {
    if (containerWidth > 0 && containerWidth < 550) return "column";
    if (containerWidth === 0) return layout; // SSR / first render fallback

    // 翻译模式排版方向：优先使用翻译设置，auto（或未设置）时回退到阅读模式
    const translateDirection = GetUserSetting(
      "setting.translate.layout.direction",
      settings
    );
    const userDirection =
      translateDirection === "auto" ||
      typeof translateDirection === "undefined"
        ? GetUserSetting("setting.layout.direction", settings)
        : translateDirection;

    if (userDirection === "row" || userDirection === "column") {
      return userDirection;
    }
    return layout;
  }, [containerWidth, layout, settings]);

  // Derive layout flex from mode — no setState in effect
  const layoutFlex = useMemo<ILayoutFlex>(() => {
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
    }

    switch (currMode) {
      case "wbw":
        return { left: 7, right: 3 };
      case "edit":
      default:
        return { left: 5, right: 5 };
    }
  }, [book, mode, newMode, para]);

  // Sync sentence data to store
  useEffect(() => {
    store.dispatch(
      push({
        id: `${book}-${para}-${wordStart}-${wordEnd}`,
        origin: origin?.map((item) => item.html),
        translation: translation?.map((item) => item.html),
      })
    );
  }, [book, origin, para, translation, wordEnd, wordStart]);

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
            if (item.channel.type === "nissaya") {
              return (
                <NissayaSent key={id} data={JSON.parse(item.content ?? "[]")} />
              );
            } else {
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
                    onWbwChange?.(data);
                  }}
                  onMagicDictDone={() => {
                    onMagicDictDone?.();
                  }}
                />
              );
            }
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
