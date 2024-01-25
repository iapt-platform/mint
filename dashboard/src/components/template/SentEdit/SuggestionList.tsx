import { Button, List, message, Skeleton, Space, Switch } from "antd";
import { useEffect, useState } from "react";
import { ReloadOutlined } from "@ant-design/icons";

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
  content?: string | null;
  channel: IChannel;
  enable?: boolean;
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
  content,
  reload = false,
  enable = true,
  onReload,
  onChange,
}: IWidget) => {
  const [sentData, setSentData] = useState<ISentence[]>([]);
  const [loading, setLoading] = useState(false);
  const [showDiff, setShowDiff] = useState(true);
  const load = () => {
    if (!enable) {
      return;
    }
    const url = `/v2/sentpr?view=sent-info&book=${book}&para=${para}&start=${wordStart}&end=${wordEnd}&channel=${channel.id}`;
    console.log("url", url);
    setLoading(true);
    get<ISuggestionListResponse>(url)
      .then((json) => {
        if (json.ok) {
          const newData: ISentence[] = json.data.rows.map((item) => {
            return {
              id: item.id,
              uid: item.uid,
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
        setLoading(false);
        if (reload && typeof onReload !== "undefined") {
          onReload();
        }
      });
  };
  useEffect(() => {
    load();
  }, [book, channel.id, para, reload, wordEnd, wordStart]);
  useEffect(() => {
    if (reload) {
      load();
    }
  }, [reload]);
  return (
    <>
      {loading ? (
        <Skeleton />
      ) : (
        <>
          <List
            header={
              <div style={{ textAlign: "right" }}>
                <Space>
                  <Button
                    type="link"
                    size="small"
                    icon={<ReloadOutlined />}
                    onClick={() => load()}
                  ></Button>
                  {"文本比对"}
                  <Switch
                    size="small"
                    defaultChecked
                    onChange={(checked) => setShowDiff(checked)}
                  />
                </Space>
              </div>
            }
            itemLayout="vertical"
            size="small"
            dataSource={sentData}
            renderItem={(item, id) => (
              <List.Item>
                <SentCell
                  value={item}
                  key={id}
                  isPr={true}
                  showDiff={showDiff}
                  diffText={content}
                  onDelete={() => load()}
                  onChange={() => load()}
                />
              </List.Item>
            )}
          />
        </>
      )}
    </>
  );
};

export default SuggestionListWidget;
