import { Space, Tooltip, Typography } from "antd";
import { Change, diffChars } from "diff";

import User from "../auth/User";
import TimeShow from "../general/TimeShow";
import { ISentHistoryData } from "./SentHistory";

const { Text, Paragraph } = Typography;

interface IWidget {
  data?: ISentHistoryData;
  oldContent?: string;
}
const SentHistoryItemWidget = ({ data, oldContent }: IWidget) => {
  let content = <Text>{data?.content}</Text>;
  if (data?.content && oldContent) {
    const diff: Change[] = diffChars(oldContent, data.content);
    const diffResult = diff.map((item, id) => {
      return (
        <Text
          key={id}
          type={item.added ? "success" : item.removed ? "danger" : "secondary"}
          delete={item.removed ? true : undefined}
        >
          {item.value}
        </Text>
      );
    });
    content = <Tooltip title={data.content}>{diffResult}</Tooltip>;
  }
  return (
    <Paragraph style={{ paddingLeft: 12 }}>
      <blockquote>
        {content}
        <div>
          <Space style={{ fontSize: "80%" }}>
            <User {...data?.editor} showAvatar={false} />
            <TimeShow type="secondary" createdAt={data?.created_at} />
          </Space>
        </div>
      </blockquote>
    </Paragraph>
  );
};

export default SentHistoryItemWidget;
