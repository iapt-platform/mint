import { useEffect, useState } from "react";

import { get } from "../../../request";
import { ISuggestionListResponse } from "../../api/Suggestion";
import { IChannel } from "../../channel/Channel";
import { ISentence } from "../SentEdit";
import SentCell from "./SentCell";
interface IWidget {
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
  channel: IChannel;
}
const Widget = ({ book, para, wordStart, wordEnd, channel }: IWidget) => {
  const [sentData, setSentData] = useState<ISentence[]>([]);

  useEffect(() => {
    get<ISuggestionListResponse>(
      `/v2/sentpr?view=sent-info&book=${book}&para=${para}&start=${wordStart}&end=${wordEnd}&channel=${channel.id}`
    ).then((json) => {
      const newData: ISentence[] = json.data.rows.map((item) => {
        return {
          content: item.content,
          html: item.html,
          book: item.book,
          para: item.paragraph,
          wordStart: item.word_start,
          wordEnd: item.word_end,
          editor: item.editor,
          channel: { name: item.channel.name, id: item.channel.id },
          updateAt: item.updated_at,
        };
      });
      setSentData(newData);
    });
  }, [book, para, wordStart, wordEnd, channel]);
  return (
    <div>
      {sentData.map((item, id) => {
        return <SentCell data={item} key={id} />;
      })}
    </div>
  );
};

export default Widget;
