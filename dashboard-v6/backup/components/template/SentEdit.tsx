import { Affix } from "antd";
import { useEffect, useRef, useState } from "react";
import type { IStudio } from "../auth/Studio";

import type { IUser } from "../auth/User";
import type { IChannel } from "../channel/Channel";
import type { TContentType } from "../discussion/DiscussionCreate";
import type { ITocPathNode } from "../../../src/components/tipitaka/TocPath";
import SentContent from "./SentEdit/SentContent";
import SentTab from "./SentEdit/SentTab";
import type { IWbw } from "./Wbw/WbwWord";
import type { ArticleMode } from "../article/Article";
import type { TChannelType } from "../../api/Channel";
import { useAppSelector } from "../../hooks";
import { currFocus } from "../../reducers/focus";
import type { ISentenceData } from "../../api/Corpus";
import "./SentEdit/style.css";
import SentCell from "./SentEdit/SentCell";
import { settingInfo } from "../../reducers/setting";
import { GetUserSetting } from "../auth/setting/default";
import { SENTENCE_FIX_WIDTH } from "../../types/article";
import { useSetting } from "../../hooks/useSetting";

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
  ___channels,
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
  const [loadedRes, setLoadedRes] = useState<IResNumber>();
  const [isFocus, setIsFocus] = useState(false);
  const focus = useAppSelector(currFocus);
  const divRef = useRef<HTMLDivElement>(null);
  const [affix, setAffix] = useState<boolean>(false);
  const settings = useAppSelector(settingInfo);
  const [commentaryLayout, setCommentaryLayout] = useState("column");
  const rootFixed = useSetting("setting.layout.root.fixed");

  useEffect(() => {
    const layoutCommentary = GetUserSetting(
      "setting.layout.commentary",
      settings
    );

    layoutCommentary &&
      typeof layoutCommentary === "string" &&
      setCommentaryLayout(layoutCommentary);
  }, [settings]);

  useEffect(() => {
    if (focus) {
      if (focus.focus?.type === "sentence") {
        if (focus.focus.id === id) {
          setIsFocus(true);
          divRef.current?.scrollIntoView({
            behavior: "smooth",
            block: "nearest",
            inline: "nearest",
          });
        } else {
          setIsFocus(false);
        }
      }
    } else {
      setIsFocus(false);
    }
  }, [focus, id]);

  useEffect(() => {
    const validRes = (value: ISentence, type: TChannelType) =>
      value.channel.type === type &&
      value.content &&
      value.content.trim().length > 0;
    if (translation) {
      const res = {
        translation: translation.filter((value) =>
          validRes(value, "translation")
        ).length,
        nissaya: translation.filter((value) => validRes(value, "nissaya"))
          .length,
        commentary: translation.filter((value) => validRes(value, "commentary"))
          .length,
      };
      setLoadedRes(res);
    }
  }, [translation]);

  useEffect(() => {
    const content = origin?.find(
      (value) => value.contentType === "json"
    )?.content;
    if (content) {
      setWbwData(JSON.parse(content));
    }
  }, []);

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

interface IWidgetSentEdit {
  props: string;
}
const Widget = ({ props }: IWidgetSentEdit) => {
  const prop = JSON.parse(atob(props)) as IWidgetSentEditInner;
  //console.log("sent data", prop);
  return <SentEditInner {...prop} />;
};

export default Widget;
