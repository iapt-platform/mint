import { Button, List, message, Skeleton, Space, Switch } from "antd";
import { useEffect, useState } from "react";
import { ReloadOutlined } from "@ant-design/icons";

import { get } from "../../request";
import type { ISuggestionListResponse } from "../../api/Suggestion";

import type { ISentence } from "../../api/sentence";
import SentCell from "./SentCell";
import type { IChannel } from "../../api/channel";
interface IWidget {
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
  content?: string | null;
  channel: IChannel;
  enable?: boolean;
  reload?: boolean;
  onReload?: () => void;
  onChange?: (count: number) => void;
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
  const [refreshCount, setRefreshCount] = useState(0);

  useEffect(() => {
    if (!enable) {
      return;
    }

    const controller = new AbortController();

    const fetchData = async () => {
      const url = `/v2/sentpr?view=sent-info&book=${book}&para=${para}&start=${wordStart}&end=${wordEnd}&channel=${channel.id}`;
      console.log("url", url);
      setLoading(true);
      try {
        const json = await get<ISuggestionListResponse>(url);
        if (json.ok) {
          const newData: ISentence[] = json.data.rows.map((item) => ({
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
          }));
          setSentData(newData);
          onChange?.(json.data.count);
        } else {
          message.error(json.message);
        }
      } catch (e) {
        if ((e as Error).name === "AbortError") {
          // 请求被取消，忽略，不更新状态
          return;
        }
        message.error((e as Error).message);
      } finally {
        // 只有请求未被取消时才更新 loading 状态
        if (!controller.signal.aborted) {
          setLoading(false);
          if (reload) {
            onReload?.();
          }
        }
      }
    };

    fetchData();

    // cleanup：依赖变化或组件卸载时取消上一次请求
    return () => {
      controller.abort();
    };

    //FIXME reload 由 true 变为 false 的时候会再次刷新
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [
    book,
    channel.id,
    para,
    wordEnd,
    wordStart,
    enable,
    reload,
    refreshCount,
  ]);
  //                                                                ^^^^^^^^^^^
  // reload 保留在依赖中，外部 reload prop 变化时也会触发；
  // refreshCount 变化时触发手动刷新。

  const handleRefresh = () => setRefreshCount((c) => c + 1);

  return (
    <>
      {loading ? (
        <Skeleton />
      ) : (
        <List
          header={
            <div style={{ textAlign: "right" }}>
              <Space>
                <Button
                  type="link"
                  size="small"
                  icon={<ReloadOutlined />}
                  onClick={handleRefresh}
                />
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
                onDelete={handleRefresh}
                onChange={handleRefresh}
              />
            </List.Item>
          )}
        />
      )}
    </>
  );
};

export default SuggestionListWidget;
