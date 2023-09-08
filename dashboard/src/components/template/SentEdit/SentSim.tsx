import { Button, message } from "antd";
import { useEffect, useState } from "react";
import { ReloadOutlined } from "@ant-design/icons";

import { get } from "../../../request";
import { ISentenceSimListResponse } from "../../api/Corpus";
import MdView from "../MdView";

interface IWidget {
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
  channelsId?: string[];
  limit?: number;
  reload?: boolean;
  onReload?: Function;
}
const SentSimWidget = ({
  book,
  para,
  wordStart,
  wordEnd,
  limit,
  channelsId,
  reload = false,
  onReload,
}: IWidget) => {
  const [sentData, setSentData] = useState<string[]>([]);

  const load = () => {
    let url = `/v2/sent-sim?view=sentence&book=${book}&paragraph=${para}&start=${wordStart}&end=${wordEnd}&limit=10&mode=edit`;
    if (typeof limit !== "undefined") {
      url = url + `&limit=${limit}`;
    }
    url += channelsId ? `&channels=${channelsId.join()}` : "";
    get<ISentenceSimListResponse>(url)
      .then((json) => {
        if (json.ok) {
          console.log("pr load", json.data.rows);
          setSentData(json.data.rows);
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
        return <MdView html={item} key={id} />;
      })}
    </>
  );
};

export default SentSimWidget;
