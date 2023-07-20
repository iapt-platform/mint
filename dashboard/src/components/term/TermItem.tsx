import { Typography } from "antd";
import { ITermDataResponse } from "../api/Term";
import MdView from "../template/MdView";

const { Title, Text, Paragraph } = Typography;

interface IWidget {
  data?: ITermDataResponse;
}
const Widget = ({ data }: IWidget) => {
  return (
    <Paragraph>
      <Title level={4}>{data?.meaning}</Title>
      <Text>{data?.other_meaning}</Text>
      <MdView html={data?.html} />
    </Paragraph>
  );
};

export default Widget;
