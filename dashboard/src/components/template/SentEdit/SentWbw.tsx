import { List, message } from "antd";
import { useEffect, useState } from "react";

import { get } from "../../../request";
import { ISentenceWbwListResponse } from "../../api/Corpus";
import { IWidgetSentEditInner, SentEditInner } from "../SentEdit";

interface IWidget {
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
  channelsId?: string[];
  reload?: boolean;
  onReload?: Function;
}
const SentWbwWidget = ({
  book,
  para,
  wordStart,
  wordEnd,
  channelsId,
  reload = false,
  onReload,
}: IWidget) => {
  const [initLoading, setInitLoading] = useState(true);

  const [sentData, setSentData] = useState<IWidgetSentEditInner[]>([]);

  const load = () => {
    let url = `/v2/wbw-sentence?view=sent-can-read&book=${book}&para=${para}&wordStart=${wordStart}&wordEnd=${wordEnd}`;
    if (channelsId && channelsId.length > 0) {
      url += `&exclude=${channelsId[0]}`;
    }

    console.log("url", url);
    get<ISentenceWbwListResponse>(url)
      .then((json) => {
        if (json.ok) {
          console.log("sim load", json.data.count);
          setSentData(json.data.rows);
        } else {
          message.error(json.message);
        }
      })
      .finally(() => {
        setInitLoading(false);
        if (reload && typeof onReload !== "undefined") {
          onReload();
        }
      });
  };
  useEffect(() => {
    load();
  }, []);

  return (
    <>
      <List
        loading={initLoading}
        itemLayout="horizontal"
        split={false}
        dataSource={sentData}
        renderItem={(item, index) => (
          <List.Item key={index}>
            <SentEditInner {...item} />
          </List.Item>
        )}
      />
    </>
  );
};

export default SentWbwWidget;
