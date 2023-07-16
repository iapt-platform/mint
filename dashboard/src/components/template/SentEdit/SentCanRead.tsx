import { Button, message } from "antd";
import { useEffect, useState } from "react";
import { ReloadOutlined } from "@ant-design/icons";

import { get } from "../../../request";
import { TChannelType } from "../../api/Channel";
import { ISentenceListResponse } from "../../api/Corpus";

import { ISentence } from "../SentEdit";
import SentCell from "./SentCell";
import SentAdd from "./SentAdd";
import { useAppSelector } from "../../../hooks";
import { currentUser as _currentUser } from "../../../reducers/current-user";
import { IChannel } from "../../channel/Channel";

interface IWidget {
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
  type: TChannelType;
  channelsId?: string[];
  reload?: boolean;
  onReload?: Function;
}
const SentCanReadWidget = ({
  book,
  para,
  wordStart,
  wordEnd,
  type,
  channelsId,
  reload = false,
  onReload,
}: IWidget) => {
  const [sentData, setSentData] = useState<ISentence[]>([]);
  const [channels, setChannels] = useState<string[]>();

  const user = useAppSelector(_currentUser);

  const load = () => {
    const sentId = `${book}-${para}-${wordStart}-${wordEnd}`;
    let url = `/v2/sentence?view=sent-can-read&sentence=${sentId}&type=${type}&mode=edit`;
    url += channelsId ? `&channels=${channelsId.join()}` : "";
    console.log("url", url);
    get<ISentenceListResponse>(url)
      .then((json) => {
        if (json.ok) {
          console.log("sent load", json.data.rows);
          const channels: string[] = json.data.rows.map(
            (item) => item.channel.id
          );
          setChannels(channels);
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
              suggestionCount: item.suggestionCount,
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
      <SentAdd
        disableChannels={channels}
        onSelect={(channel: IChannel) => {
          if (typeof user === "undefined") {
            return;
          }
          const sentData: ISentence = {
            content: "",
            contentType: "markdown",
            html: "",
            book: book,
            para: para,
            wordStart: wordStart,
            wordEnd: wordEnd,
            editor: {
              id: user.id,
              nickName: user.nickName,
              userName: user.realName,
            },
            channel: channel,
            updateAt: "",
            openInEditMode: true,
          };
          setSentData((origin) => {
            return [sentData, ...origin];
          });
        }}
      />

      {sentData.map((item, id) => {
        return (
          <SentCell
            data={item}
            key={id}
            isPr={false}
            editMode={item.openInEditMode}
          />
        );
      })}
    </>
  );
};

export default SentCanReadWidget;
