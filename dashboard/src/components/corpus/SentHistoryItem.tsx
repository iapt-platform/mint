import { Space, Typography } from "antd";
import User from "../auth/User";
import TimeShow from "../general/TimeShow";
import { ISentHistoryData } from "./SentHistory";

const { Text } = Typography;

interface IWidget {
  data?: ISentHistoryData;
  oldContent?: string;
}
const SentHistoryItemWidget = ({ data, oldContent }: IWidget) => {
  return (
    <div>
      <div>
        <Text>{data?.content}</Text>
      </div>
      <div>
        <Space style={{ fontSize: "80%" }}>
          <User {...data?.editor} />
          <TimeShow type="secondary" createdAt={data?.created_at} />
        </Space>
      </div>
    </div>
  );
};

export default SentHistoryItemWidget;
