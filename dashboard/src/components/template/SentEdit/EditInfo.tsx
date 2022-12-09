import { Typography } from "antd";
import { Space } from "antd";
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
          <span>updated</span>
          <TimeShow time={data.updateAt} title="UpdatedAt" />
        </Space>
      </Text>
    </div>
  );
};

export default Widget;
