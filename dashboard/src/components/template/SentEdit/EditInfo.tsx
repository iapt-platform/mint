import { Typography } from "antd";
import { Space } from "antd";

import StudioName from "../../auth/StudioName";
import User from "../../auth/User";
import Channel from "../../channel/Channel";
import TimeShow from "../../general/TimeShow";
import { ISentence } from "../SentEdit";

const { Text } = Typography;

interface IWidget {
  data: ISentence;
  isPr?: boolean;
  compact?: boolean;
}
const EditInfoWidget = ({ data, isPr = false, compact = false }: IWidget) => {
  const details = (
    <Space>
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
    </Space>
  );
  return (
    <div style={{ fontSize: "80%" }}>
      <Text type="secondary">
        <Space>
          {isPr ? undefined : (
            <StudioName
              data={data.studio}
              showName={false}
              popOver={compact ? details : undefined}
            />
          )}
          {compact ? undefined : details}
        </Space>
      </Text>
    </div>
  );
};

export default EditInfoWidget;
