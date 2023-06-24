import { message } from "antd";
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
  reload?: boolean;
  onReload?: Function;
  onChange?: Function;
}
const SuggestionListWidget = ({
  book,
  para,
  wordStart,
  wordEnd,
  channel,
  reload = false,
  onReload,
  onChange,
}: IWidget) => {
  const [sentData, setSentData] = useState<ISentence[]>([]);

  const load = () => {
    get<ISuggestionListResponse>(
      `/v2/sentpr?view=sent-info&book=${book}&para=${para}&start=${wordStart}&end=${wordEnd}&channel=${channel.id}`
    )
      .then((json) => {
        if (json.ok) {
          console.log("pr load", json.data.rows);
          const newData: ISentence[] = json.data.rows.map((item) => {
            return {
              id: item.id,
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
          if (typeof onChange !== "undefined") {
            onChange(json.data.count);
          }
        } else {
          message.error(json.message);
        }
      })
      .finally(() => {
        if (reload && typeof onReload !== "undefined") {
          onReload();
        }
      });
  };
  useEffect(() => {
    load();
  }, []);
  useEffect(() => {
    if (reload) {
      load();
    }
  }, [reload]);
  return (
    <>
      {sentData.map((item, id) => {
        return <SentCell data={item} key={id} isPr={true} />;
      })}
    </>
  );
};

export default SuggestionListWidget;
