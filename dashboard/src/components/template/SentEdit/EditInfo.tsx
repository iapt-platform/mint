import { List, Popover, Typography, notification } from "antd";
import { Space } from "antd";

import User from "../../auth/User";
import Channel from "../../channel/Channel";
import TimeShow from "../../general/TimeShow";
import { ISentence } from "../SentEdit";
import { MergeIcon2 } from "../../../assets/icon";
import { useEffect, useState } from "react";
import { get } from "../../../request";
import {
  ISentHistoryData,
  ISentHistoryListResponse,
} from "../../corpus/SentHistory";
import moment from "moment";

const { Text } = Typography;

interface IFork {
  sentId?: string;
  highlight?: boolean;
}
const Fork = ({ sentId, highlight = false }: IFork) => {
  const [data, setData] = useState<ISentHistoryData[]>();

  useEffect(() => {
    if (sentId) {
      const url = `/v2/sent_history?view=sentence&id=${sentId}&fork=1`;
      get<ISentHistoryListResponse>(url).then((json) => {
        if (json.ok) {
          setData(json.data.rows);
        } else {
          notification.error({ message: json.message });
        }
      });
    }
  }, [sentId]);
  return (
    <Popover
      placement="bottom"
      content={
        <List
          size="small"
          header={highlight ? false : "已被修改"}
          footer={false}
          dataSource={data}
          renderItem={(item) => (
            <List.Item>
              <Text type="secondary" style={{ fontSize: "85%" }}>
                <Space>
                  <User {...item.accepter} showAvatar={false} />
                  {"fork from"}
                  <Text>
                    {item.fork_studio?.nickName}-{item.fork_from?.name}
                  </Text>
                  {"on"}
                  <TimeShow
                    type="secondary"
                    title="复制"
                    showLabel={false}
                    createdAt={item.created_at}
                  />
                </Space>
              </Text>
            </List.Item>
          )}
        />
      }
    >
      <span style={{ color: highlight ? "#1890ff" : "unset" }}>
        <MergeIcon2 />
      </span>
    </Popover>
  );
};

interface IMergeButton {
  data: ISentence;
}
const MergeButton = ({ data }: IMergeButton) => {
  if (data.forkAt) {
    const fork = moment(data.forkAt);
    const updated = moment(data.updateAt);
    if (fork.isSame(updated)) {
      return <Fork sentId={data.id} highlight />;
    } else {
      return <Fork sentId={data.id} />;
    }
  } else {
    return <></>;
  }
};

interface IDetailsWidget {
  data: ISentence;
  isPr?: boolean;
}

export const Details = ({ data, isPr }: IDetailsWidget) => (
  <Space wrap>
    {isPr ? <></> : <Channel {...data.channel} />}
    <User {...data.editor} showAvatar={false} />
    {data.prEditAt ? (
      <TimeShow
        type="secondary"
        updatedAt={data.prEditAt}
        createdAt={data.createdAt}
      />
    ) : (
      <TimeShow
        type="secondary"
        updatedAt={data.updateAt}
        createdAt={data.createdAt}
      />
    )}
    <MergeButton data={data} />
    <span style={{ display: "none" }}>
      {data.acceptor ? (
        <User {...data.acceptor} showAvatar={false} />
      ) : undefined}
      {data.acceptor ? "accept at" : undefined}
      {data.prEditAt ? (
        <TimeShow
          type="secondary"
          updatedAt={data.updateAt}
          showLabel={false}
        />
      ) : undefined}
    </span>
  </Space>
);

interface IWidget {
  data: ISentence;
  isPr?: boolean;
  compact?: boolean;
}
const EditInfoWidget = ({ data, isPr = false, compact = false }: IWidget) => {
  return (
    <div style={{ fontSize: "80%" }}>
      <Text type="secondary">
        {compact ? undefined : <Details data={data} isPr={isPr} />}
      </Text>
    </div>
  );
};

export default EditInfoWidget;
