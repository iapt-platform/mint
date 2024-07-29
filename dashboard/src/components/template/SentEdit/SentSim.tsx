import { Button, Divider, List, Space, Switch, message } from "antd";
import { useEffect, useState } from "react";
import { ReloadOutlined } from "@ant-design/icons";

import { get } from "../../../request";
import { ISentenceSimListResponse, ISimSent } from "../../api/Corpus";
import MdView from "../MdView";
import SentCanRead from "./SentCanRead";

interface IWidget {
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
  channelsId?: string[];
  limit?: number;
  reload?: boolean;
  onReload?: Function;
  onCreate?: Function;
}
const SentSimWidget = ({
  book,
  para,
  wordStart,
  wordEnd,
  limit = 5,
  channelsId,
  reload = false,
  onReload,
  onCreate,
}: IWidget) => {
  const [initLoading, setInitLoading] = useState(true);
  const [loading, setLoading] = useState(false);
  const [sim, setSim] = useState(0);
  const [offset, setOffset] = useState(0);
  const [sentData, setSentData] = useState<ISimSent[]>([]);
  const [remain, setRemain] = useState(0);

  const load = () => {
    let url = `/v2/sent-sim?view=sentence&book=${book}&paragraph=${para}&start=${wordStart}&end=${wordEnd}&mode=edit`;
    url += `&limit=${limit}`;
    url += `&offset=${offset}`;
    url += `&sim=${sim}`;

    url += channelsId ? `&channels=${channelsId.join()}` : "";
    setLoading(true);
    console.log("url", url);
    get<ISentenceSimListResponse>(url)
      .then((json) => {
        if (json.ok) {
          console.log("sim load", json.data.rows);
          setSentData([...sentData, ...json.data.rows]);
          setRemain(json.data.count - sentData.length - json.data.rows.length);
        } else {
          message.error(json.message);
        }
      })
      .finally(() => {
        setInitLoading(false);
        setLoading(false);
        if (reload && typeof onReload !== "undefined") {
          onReload();
        }
      });
  };
  useEffect(() => {
    load();
  }, [offset, sim]);

  return (
    <>
      <SentCanRead
        book={book}
        para={para}
        wordStart={wordStart}
        wordEnd={wordEnd}
        type="similar"
        channelsId={channelsId}
        onCreate={onCreate}
      />
      <List
        loading={initLoading}
        header={
          <div style={{ display: "flex", justifyContent: "space-between" }}>
            <span></span>
            <Space>
              {"只显示相同句"}
              <Switch
                onChange={(checked: boolean) => {
                  if (checked) {
                    setSim(1);
                  } else {
                    setSim(0);
                  }
                  setOffset(0);
                  setSentData([]);
                }}
              />
              <Button
                type="link"
                shape="round"
                icon={<ReloadOutlined />}
                onClick={() => {}}
              />
            </Space>
          </div>
        }
        itemLayout="horizontal"
        split={false}
        loadMore={
          <Divider>
            <Button
              disabled={remain <= 0}
              onClick={() => {
                setOffset((origin) => origin + limit);
              }}
              loading={loading}
            >
              load more
            </Button>
          </Divider>
        }
        dataSource={sentData}
        renderItem={(item, index) => (
          <List.Item>
            <MdView html={item.sent} key={index} style={{ width: "100%" }} />
          </List.Item>
        )}
      />
    </>
  );
};

export default SentSimWidget;
