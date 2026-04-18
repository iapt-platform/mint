import { Button, message } from "antd";
import { useCallback, useEffect, useState } from "react";
import { ReloadOutlined } from "@ant-design/icons";

import { get } from "../../request";
import type { IChannel, TChannelType } from "../../api/channel";
import type { ISentence, ISentenceListResponse } from "../../api/sentence";

import SentCell from "./SentCell";
import SentAdd from "./SentAdd";
import { useAppSelector } from "../../hooks";
import { currentUser as _currentUser } from "../../reducers/current-user";
import { toISentence } from "../sentence/utils";
import type { IWbw } from "../../types/wbw";

interface IWidget {
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
  type: TChannelType;
  channelsId?: string[];
  origin?: ISentence[];
  onReload?: () => void;
  onCreate?: () => void;
}
const SentCanReadWidget = ({
  book,
  para,
  wordStart,
  wordEnd,
  type,
  channelsId,
  origin,
  onReload,
  onCreate,
}: IWidget) => {
  const [sentData, setSentData] = useState<ISentence[]>([]);
  const [channels, setChannels] = useState<string[]>();
  const user = useAppSelector(_currentUser);

  const load = useCallback(() => {
    const sentId = `${book}-${para}-${wordStart}-${wordEnd}`;
    let url = `/api/v2/sentence?view=sent-can-read&sentence=${sentId}&type=${type}&mode=edit&html=true`;
    url += channelsId ? `&excludes=${channelsId.join()}` : "";
    if (type === "commentary" || type === "similar") {
      url += channelsId ? `&channels=${channelsId.join()}` : "";
    }
    console.info("ai request", url);
    get<ISentenceListResponse>(url)
      .then((json) => {
        if (json.ok) {
          const channels: string[] = json.data.rows.map(
            (item) => item.channel.id
          );
          setChannels(channels);
          const newData: ISentence[] = json.data.rows.map((item) =>
            toISentence(item, channelsId)
          );
          setSentData(newData);
        } else {
          message.error(json.message);
        }
      })
      .finally(() => {
        onReload?.();
      });
  }, [book, para, wordStart, wordEnd, type, channelsId, onReload]);

  useEffect(() => {
    load();
  }, [load]);

  return (
    <div>
      <div style={{ display: "flex", justifyContent: "space-between" }}>
        <span></span>
        <Button
          type="link"
          shape="round"
          icon={<ReloadOutlined />}
          onClick={() => load()}
        />
      </div>
      <div style={{ textAlign: "center" }}>
        <SentAdd
          disableChannels={channels}
          type={type}
          onSelect={(channel: IChannel) => {
            if (typeof user === "undefined") {
              return;
            }
            const newSent: ISentence = {
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
              translationChannels: channelsId,
              updateAt: "",
              openInEditMode: true,
            };
            setSentData((origin) => {
              return [newSent, ...origin];
            });

            setChannels((origin) => {
              if (origin) {
                if (!origin.includes(newSent.channel.id)) {
                  origin.push(newSent.channel.id);
                  return origin;
                }
              } else {
                return [newSent.channel.id];
              }
            });
            if (typeof onCreate !== "undefined") {
              onCreate();
            }
          }}
        />
      </div>
      {sentData.map((item, id) => {
        let diffText: string | null = null;
        if (origin) {
          diffText = origin[0].html;
          if (origin[0].contentType === "json" && origin[0].content) {
            const wbw = JSON.parse(origin[0].content) as IWbw[];
            console.debug("wbw data", wbw);
            diffText = wbw
              .filter((value) => {
                if (value.style && value.style.value === "note") {
                  return false;
                } else if (value.type && value.type.value === ".ctl.") {
                  return false;
                } else {
                  return true;
                }
              })
              .map(
                (item) =>
                  `${item.word.value
                    .replaceAll("{", "**")
                    .replaceAll("}", "**")}`
              )
              .join(" ");
          }
          console.debug("origin", origin);
        }

        return (
          <SentCell
            value={item}
            key={id}
            isPr={false}
            diffText={diffText}
            showDiff={origin ? true : false}
            editMode={item.openInEditMode}
            onChange={(value: ISentence) => {
              console.debug("onChange", value);
              setSentData((origin) => {
                origin.forEach((value1, index, array) => {
                  if (value1.id === value.id) {
                    array[index] = value;
                  }
                });
                return origin;
              });
            }}
          />
        );
      })}
    </div>
  );
};

export default SentCanReadWidget;
