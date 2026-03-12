import { Button, Divider, List, Space, Switch } from "antd";
import { ReloadOutlined } from "@ant-design/icons";

import type {
  ISentenceSimListResponse,
  ISentSimParams,
} from "../../api/sent-sim";
import { useSentSim } from "../../hooks/useSentSim";
import SentCanRead from "./SentCanRead";
import MdView from "../general/MdView";

type Fetcher = (params: ISentSimParams) => Promise<ISentenceSimListResponse>;

interface IWidget {
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
  channelsId?: string[];
  limit?: number;
  /** 可替换为 mock 函数，默认使用真实请求 */
  fetcher?: Fetcher;
  onCreate?: () => void;
}

const SentSimWidget = ({
  book,
  para,
  wordStart,
  wordEnd,
  limit = 5,
  channelsId,
  fetcher,
  onCreate,
}: IWidget) => {
  const {
    sentData,
    remain,
    initLoading,
    loading,
    toggleSim,
    loadMore,
    reload,
  } = useSentSim({
    book,
    para,
    wordStart,
    wordEnd,
    limit,
    channelsId,
    fetcher,
  });

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
            <span />
            <Space>
              {"只显示相同句"}
              <Switch onChange={toggleSim} />
              <Button
                type="link"
                shape="round"
                icon={<ReloadOutlined />}
                loading={loading}
                onClick={reload}
              />
            </Space>
          </div>
        }
        itemLayout="horizontal"
        split={false}
        loadMore={
          <Divider>
            <Button disabled={remain <= 0} onClick={loadMore} loading={loading}>
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
