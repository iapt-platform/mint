import { Typography } from "antd";
import { Space } from "antd";
import { useEffect, useRef } from "react";

import User from "../../auth/User";
import TimeShow from "../../general/TimeShow";
import { ISentence } from "../SentEdit";

const { Text } = Typography;

interface IWidget {
  data: ISentence;
}
const Widget = ({ data }: IWidget) => {
  return (
    <div style={{ fontSize: "80%" }}>
      <Text type="secondary">
        <Space>
          <User {...data.editor} />
          <span>edit</span>
          {data.prEditAt ? (
            <TimeShow time={data.prEditAt} />
          ) : (
            <TimeShow time={data.updateAt} />
          )}
          {data.acceptor ? (
            <User {...data.acceptor} showAvatar={false} />
          ) : undefined}
          {data.acceptor ? "accept at" : undefined}
          {data.prEditAt ? <TimeShow time={data.updateAt} /> : undefined}
        </Space>
      </Text>
    </div>
  );
};

export default Widget;
