import { Button, message } from "antd";
import { useEffect, useState } from "react";
import { ReloadOutlined } from "@ant-design/icons";

import { get } from "../../../request";
import { TChannelType } from "../../api/Channel";
import { ISentenceListResponse } from "../../api/Corpus";

import { ISentence } from "../SentEdit";
import SentCell from "./SentCell";
interface IWidget {
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
  type: TChannelType;
  reload?: boolean;
  onReload?: Function;
}
const Widget = ({
  book,
  para,
  wordStart,
  wordEnd,
  type,
  reload = false,
  onReload,
}: IWidget) => {
  const [sentData, setSentData] = useState<ISentence[]>([]);

  const load = () => {
    get<ISentenceListResponse>(
      `/v2/sentence?view=sent-can-read&sentence=${book}-${para}-${wordStart}-${wordEnd}&type=${type}&mode=edit`
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
              studio: item.studio,
              channel: item.channel,
              updateAt: item.updated_at,
            };
          });
          setSentData(newData);
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
      <div style={{ display: "flex", justifyContent: "space-between" }}>
        <span></span>
        <Button
          type="link"
          shape="round"
          icon={<ReloadOutlined />}
          onClick={() => load()}
        />
      </div>
      {sentData.map((item, id) => {
        return <SentCell data={item} key={id} isPr={true} />;
      })}
    </>
  );
};

export default Widget;
