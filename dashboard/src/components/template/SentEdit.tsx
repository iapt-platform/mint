import { Card } from "antd";
import { useEffect, useState } from "react";
import { IStudio } from "../auth/StudioName";

import type { IUser } from "../auth/User";
import { IChannel } from "../channel/Channel";
import { TContentType } from "../discussion/DiscussionCreate";
import { ITocPathNode } from "../corpus/TocPath";
import SentContent from "./SentEdit/SentContent";
import SentTab from "./SentEdit/SentTab";
import { IWbw } from "./Wbw/WbwWord";
import { ArticleMode } from "../article/Article";

export interface ISuggestionCount {
  suggestion?: number;
  discussion?: number;
}
export interface ISentence {
  id?: string;
  content: string;
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
export interface IWidgetSentEditInner {
  id: string;
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
  channels?: string[];
  origin?: ISentence[];
  translation?: ISentence[];
  path?: ITocPathNode[];
  layout?: "row" | "column";
  tranNum?: number;
  nissayaNum?: number;
  commNum?: number;
  originNum: number;
  simNum?: number;
  compact?: boolean;
  mode?: ArticleMode;
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
  path,
  layout = "column",
  tranNum,
  nissayaNum,
  commNum,
  originNum,
  simNum,
  compact = false,
  mode,
}: IWidgetSentEditInner) => {
  const [wbwData, setWbwData] = useState<IWbw[]>();
  const [magicDict, setMagicDict] = useState<string>();
  const [magicDictLoading, setMagicDictLoading] = useState(false);
  const [isCompact, setIsCompact] = useState(compact);
  const [articleMode, setArticleMode] = useState<ArticleMode | undefined>(mode);

  useEffect(() => {
    const content = origin?.find(
      (value) => value.channel.type === "wbw"
    )?.content;
    if (typeof content !== "undefined") {
      setWbwData(JSON.parse(content));
    }
  }, []);

  const channelsId = translation?.map((item) => item.channel.id);

  return (
    <Card
      bodyStyle={{ paddingBottom: 0 }}
      style={{ border: "solid 2px #dfdfdf", marginTop: 4, borderRadius: 5 }}
      size="small"
    >
      <SentContent
        sid={id}
        book={book}
        para={para}
        wordStart={wordStart}
        wordEnd={wordEnd}
        origin={origin}
        translation={translation}
        layout={layout}
        magicDict={magicDict}
        compact={isCompact}
        mode={articleMode}
        onWbwChange={(data: IWbw[]) => {
          setWbwData(data);
        }}
        onMagicDictDone={() => {
          setMagicDictLoading(false);
          setMagicDict(undefined);
        }}
      />
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
