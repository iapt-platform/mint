import { Affix } from "antd";
import { useEffect, useRef, useState, useMemo } from "react";

import type { TChannelType } from "../../api/Channel";
import { useAppSelector } from "../../hooks";
import { currFocus } from "../../reducers/focus";

import "./style.css";

import { settingInfo } from "../../reducers/setting";

import { useSetting } from "../../hooks/useSetting";
import type { ArticleMode } from "../../api/Article";
import { GetUserSetting } from "../setting/default";
import SentContent from "./SentContent";
import type { IWbw } from "../../types/wbw";
import SentTab from "./SentTab";
import { SENTENCE_FIX_WIDTH } from "../../types/article";
import SentCell from "./SentCell";
import type { ISentence } from "../../api/sentence";
import type { ITocPathNode } from "../../api/pali-text";

export interface IResNumber {
  translation?: number;
  nissaya?: number;
  commentary?: number;
  origin?: number;
  sim?: number;
}

export interface ISentenceId {
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
}

export interface IWidgetSentEditInner {
  id: string;
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
  channels?: string[];
  origin?: ISentence[];
  translation?: ISentence[];
  commentaries?: ISentence[];
  answer?: ISentence;
  path?: ITocPathNode[];
  layout?: "row" | "column";
  tranNum?: number;
  nissayaNum?: number;
  commNum?: number;
  originNum: number;
  simNum?: number;
  compact?: boolean;
  mode?: ArticleMode;
  showWbwProgress?: boolean;
  readonly?: boolean;
  wbwProgress?: number;
  wbwScore?: number;

  onTranslationChange?: (data: ISentence) => void;
}

export const SentEditInner = ({
  id,
  book,
  para,
  wordStart,
  wordEnd,
  origin,
  translation,
  answer,
  path,
  layout = "column",
  tranNum,
  nissayaNum,
  commNum,
  originNum,
  simNum,
  compact = false,
  mode,
  showWbwProgress = false,
  readonly = false,
  commentaries,
  onTranslationChange,
}: IWidgetSentEditInner) => {
  const [wbwData, setWbwData] = useState<IWbw[]>();
  const [magicDict, setMagicDict] = useState<string>();
  const [magicDictLoading, setMagicDictLoading] = useState(false);
  const [isCompact, setIsCompact] = useState(compact);
  const [articleMode, setArticleMode] = useState<ArticleMode | undefined>(mode);
  const [affix, setAffix] = useState<boolean>(false);

  const focus = useAppSelector(currFocus);
  const settings = useAppSelector(settingInfo);
  const divRef = useRef<HTMLDivElement>(null);
  const rootFixed = useSetting("setting.layout.root.fixed");

  // ✅ 从 settings 派生 commentaryLayout，无需 state + effect
  const commentaryLayout = useMemo<string>(() => {
    const layoutCommentary = GetUserSetting(
      "setting.layout.commentary",
      settings
    );
    return typeof layoutCommentary === "string" ? layoutCommentary : "column";
  }, [settings]);

  // ✅ 从 focus 派生 isFocus，无需 state
  const isFocus = useMemo(() => {
    return focus?.focus?.type === "sentence" && focus.focus.id === id;
  }, [focus, id]);

  // ✅ scroll 是真正的副作用，单独处理
  useEffect(() => {
    if (isFocus) {
      divRef.current?.scrollIntoView({
        behavior: "smooth",
        block: "nearest",
        inline: "nearest",
      });
    }
  }, [isFocus]);

  // ✅ 从 translation 派生 loadedRes，无需 state + effect
  const loadedRes = useMemo<IResNumber | undefined>(() => {
    if (!translation) return undefined;

    const validRes = (value: ISentence, type: TChannelType) =>
      value.channel.type === type &&
      value.content &&
      value.content.trim().length > 0;

    return {
      translation: translation.filter((value) => validRes(value, "translation"))
        .length,
      nissaya: translation.filter((value) => validRes(value, "nissaya")).length,
      commentary: translation.filter((value) => validRes(value, "commentary"))
        .length,
    };
  }, [translation]);

  // ✅ 补全 origin 依赖
  useEffect(() => {
    const content = origin?.find(
      (value) => value.contentType === "json"
    )?.content;
    if (content) {
      setWbwData(JSON.parse(content));
    }
  }, [origin]);

  const channelsId = translation?.map((item) => item.channel.id);

  const content = (
    <SentContent
      sid={id}
      book={book}
      para={para}
      wordStart={wordStart}
      wordEnd={wordEnd}
      origin={origin}
      translation={translation}
      answer={answer}
      layout={layout}
      magicDict={magicDict}
      compact={isCompact}
      mode={articleMode}
      wbwProgress={showWbwProgress}
      readonly={readonly}
      onWbwChange={(data: IWbw[]) => {
        setWbwData(data);
      }}
      onMagicDictDone={() => {
        setMagicDictLoading(false);
        setMagicDict(undefined);
      }}
      onTranslationChange={onTranslationChange}
    />
  );

  return (
    <div
      ref={divRef}
      className={`sent-edit-inner` + (isFocus ? " sent-focus" : "")}
      style={{
        display: commentaryLayout === "column" ? "block" : "flex",
        width: commentaryLayout === "column" ? "100%" : SENTENCE_FIX_WIDTH,
      }}
    >
      <div>
        {affix || rootFixed === true ? (
          <Affix offsetTop={44}>
            <div className="affix">{content}</div>
          </Affix>
        ) : (
          content
        )}
        <div
          style={{
            width: commentaryLayout === "column" ? "unset" : SENTENCE_FIX_WIDTH,
          }}
        >
          <SentTab
            id={id}
            book={book}
            para={para}
            wordStart={wordStart}
            wordEnd={wordEnd}
            channelsId={channelsId}
            path={path}
            tranNum={tranNum}
            nissayaNum={nissayaNum}
            commNum={commNum}
            originNum={originNum}
            simNum={simNum}
            loadedRes={loadedRes}
            wbwData={wbwData}
            origin={origin}
            magicDictLoading={magicDictLoading}
            compact={isCompact}
            mode={articleMode}
            onMagicDict={(type: string) => {
              setMagicDict(type);
              setMagicDictLoading(true);
            }}
            onCompact={(value: boolean) => setIsCompact(value)}
            onModeChange={(value: ArticleMode | undefined) =>
              setArticleMode(value)
            }
            onAffix={() => setAffix(!affix)}
          />
        </div>
      </div>
      <div className="pcd_sent_commentary">
        {commentaries?.map((item, id) => {
          return (
            <SentCell
              value={item}
              key={id}
              isPr={false}
              editMode={item.openInEditMode}
            />
          );
        })}
      </div>
    </div>
  );
};
