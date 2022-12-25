import { Typography, Space } from "antd";
import TimeShow from "../general/TimeShow";

import { IComment } from "./CommentItem";

const { Title, Text } = Typography;

interface IWidget {
  data?: IComment;
}
const Widget = ({ data }: IWidget) => {
  return (
    <div>
      <Title editable level={1} style={{ margin: 0 }}>
        {data?.title}
      </Title>
      <div>
        <Text type="secondary">
          <Space>
            {" "}
            {data?.user.nickName}{" "}
            <TimeShow time={data?.createdAt} title="创建" />
          </Space>
        </Text>
      </div>
    </div>
  );
};

export default Widget;
