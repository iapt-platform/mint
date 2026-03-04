import { Space, Typography } from "antd";
import type { IAnthologyDataResponse } from "../../../api/Article";
import Studio from "../../auth/Studio";
import TimeShow from "../../general/TimeShow";
import Marked from "../../general/Marked";

const { Title, Paragraph, Text } = Typography;

interface IWidget {
  data?: IAnthologyDataResponse | null;
}
const AnthologyInfo = ({ data }: IWidget) => {
  return (
    <div>
      <Title level={4}>{data?.title}</Title>

      <Text type="secondary">{data?.subtitle}</Text>

      <Paragraph>
        <Space>
          <Studio data={data?.studio} />
          <TimeShow updatedAt={data?.updated_at} />
        </Space>
      </Paragraph>

      <Paragraph>
        <Marked text={data?.summary} />
      </Paragraph>
    </div>
  );
};

export default AnthologyInfo;
