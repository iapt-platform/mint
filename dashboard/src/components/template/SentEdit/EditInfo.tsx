import { Typography } from "antd";
import { Space } from "antd";

import User from "../../auth/User";
import Channel from "../../channel/Channel";
import TimeShow from "../../general/TimeShow";
import { ISentence } from "../SentEdit";
import { MergeIcon2 } from "../../../assets/icon";

const { Text } = Typography;

interface IMergeButton {
  data: ISentence;
}
const MergeButton = ({ data }: IMergeButton) => {
  if (data.forkAt) {
    const fork = new Date(data.forkAt);
    const updated = new Date(data.updateAt);
    if (fork.getTime() === updated.getTime()) {
      return (
        <span style={{ color: "#1890ff" }}>
          <MergeIcon2 />
        </span>
      );
    } else {
      return <MergeIcon2 />;
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
    <Channel {...data.channel} />
    <User {...data.editor} showAvatar={isPr ? true : false} />
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
    {data.acceptor ? <User {...data.acceptor} showAvatar={false} /> : undefined}
    {data.acceptor ? "accept at" : undefined}
    {data.prEditAt ? (
      <TimeShow type="secondary" updatedAt={data.updateAt} showLabel={false} />
    ) : undefined}
  </Space>
);

interface IWidget {
  data: ISentence;
  isPr?: boolean;
  compact?: boolean;
}
const EditInfoWidget = ({ data, isPr = false, compact = false }: IWidget) => {
  console.debug("EditInfo", data);
  return (
    <div style={{ fontSize: "80%" }}>
      <Text type="secondary">
        {compact ? undefined : <Details data={data} isPr={isPr} />}
      </Text>
    </div>
  );
};

export default EditInfoWidget;
