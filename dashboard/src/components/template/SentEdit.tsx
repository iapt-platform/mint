import { Affix, Card } from "antd";
import { useEffect, useRef, useState } from "react";
import { IStudio } from "../auth/Studio";

import type { IUser } from "../auth/User";
import { IChannel } from "../channel/Channel";
import { TContentType } from "../discussion/DiscussionCreate";
import { ITocPathNode } from "../corpus/TocPath";
import SentContent from "./SentEdit/SentContent";
import SentTab from "./SentEdit/SentTab";
import { IWbw } from "./Wbw/WbwWord";
import { ArticleMode } from "../article/Article";
import { TChannelType } from "../api/Channel";
import { useAppSelector } from "../../hooks";
import { currFocus } from "../../reducers/focus";
import { ISentenceData } from "../api/Corpus";

export interface IResNumber {
  translation?: number;
  nissaya?: number;
  commentary?: number;
  origin?: number;
  sim?: number;
}

export interface ISuggestionCount {
  suggestion?: number;
  discussion?: number;
}
export interface ISentence {
  id?: string;
  uid?: string;
  content: string | null;
  contentType?: TContentType;
  html: string;
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
  editor: IUser;
  acceptor?: IUser;
  prEditAt?: string;
  channel: IChannel;
  studio?: IStudio;
  forkAt?: string | null;
  updateAt: string;
  createdAt?: string;
  suggestionCount?: ISuggestionCount;
  openInEditMode?: boolean;
  translationChannels?: string[];
}
export interface ISentenceId {
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
}

export const toISentence = (apiData: ISentenceData): ISentence => ({
  id: apiData.id,
  content: apiData.content,
  contentType: apiData.content_type,
  html: apiData.html,
  book: apiData.book,
  para: apiData.paragraph,
  wordStart: apiData.word_start,
  wordEnd: apiData.word_end,
  editor: apiData.editor,
  studio: apiData.studio,
  channel: apiData.channel,
  updateAt: apiData.updated_at,
  acceptor: apiData.acceptor,
  prEditAt: apiData.pr_edit_at,
  forkAt: apiData.fork_at,
  suggestionCount: apiData.suggestionCount,
});
export interface IWidgetSentEditInner {
  id: string;
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
  channels?: string[];
  origin?: ISentence[];
  translation?: ISentence[];
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
}
export const SentEditInner = ({
  id,
  book,
  para,
  wordStart,
  wordEnd,
  channels,
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
    />
  );

  return (
    <Card
      ref={divRef}
      bodyStyle={{ paddingBottom: 0, paddingLeft: 0, paddingRight: 0 }}
      style={{
        border: isFocus
          ? "2px solid rgb(0 0 200 / 50%)"
          : "1px solid rgb(128 128 128 / 10%)",
        marginTop: 4,
        borderRadius: 6,
        backgroundColor: "rgb(255 255 255 / 8%)",
        width: "100%",
      }}
      size="small"
    >
      {affix ? (
        <Affix offsetTop={44}>
          <div style={{ backgroundColor: "white" }}>{content}</div>
        </Affix>
      ) : (
        content
      )}
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
        magicDictLoading={magicDictLoading}
        compact={isCompact}
        mode={articleMode}
        onMagicDict={(type: string) => {
          setMagicDict(type);
          setMagicDictLoading(true);
        }}
        onCompact={(value: boolean) => setIsCompact(value)}
        onModeChange={(value: ArticleMode | undefined) => setArticleMode(value)}
        onAffix={() => setAffix(!affix)}
      />
    </Card>
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
