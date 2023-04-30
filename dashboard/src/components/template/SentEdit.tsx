import { Card } from "antd";
import { IStudio } from "../auth/StudioName";

import type { IUser } from "../auth/User";
import { IChannel } from "../channel/Channel";
import { ITocPathNode } from "../corpus/TocPath";
import SentContent from "./SentEdit/SentContent";
import SentMenu from "./SentEdit/SentMenu";
import SentTab from "./SentEdit/SentTab";

export interface ISuggestionCount {
  suggestion?: number;
  discussion?: number;
}
export interface ISentence {
  id?: string;
  content: string;
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
  suggestionCount?: ISuggestionCount;
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
}: IWidgetSentEditInner) => {
  const sid = id.split("-");
  return (
    <Card bodyStyle={{ paddingBottom: 0 }} size="small">
      <SentMenu book={parseInt(sid[0])} para={parseInt(sid[1])}>
        <SentContent
          sid={id}
          book={book}
          para={para}
          wordStart={wordStart}
          wordEnd={wordEnd}
          origin={origin}
          translation={translation}
          layout={layout}
        />
        <SentTab
          id={id}
          path={path}
          tranNum={tranNum}
          nissayaNum={nissayaNum}
          commNum={commNum}
          originNum={originNum}
          simNum={simNum}
        />
      </SentMenu>
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
