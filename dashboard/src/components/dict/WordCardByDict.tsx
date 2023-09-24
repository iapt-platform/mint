import { Button, Card, Popover, Space } from "antd";
import { Typography } from "antd";
import { InfoCircleOutlined } from "@ant-design/icons";

import Marked from "../general/Marked";
import MdView from "../template/MdView";

const { Title } = Typography;

export interface IWordByDict {
  dictname: string;
  description?: string;
  word: string;
  note: string;
  anchor: string;
}
interface IWidgetWordCardByDict {
  data: IWordByDict;
}
const WordCardByDictWidget = (prop: IWidgetWordCardByDict) => {
  return (
    <Card>
      <Space>
        <Title level={5} id={prop.data.anchor}>
          {prop.data.dictname}
        </Title>
        {prop.data.description ? (
          <Popover
            overlayStyle={{ maxWidth: 600 }}
            content={<Marked text={prop.data.description} />}
            placement="bottom"
          >
            <Button type="link" icon={<InfoCircleOutlined />} />
          </Popover>
        ) : undefined}
      </Space>
      <div>
        <MdView html={prop.data.note} />
      </div>
    </Card>
  );
};

export default WordCardByDictWidget;
