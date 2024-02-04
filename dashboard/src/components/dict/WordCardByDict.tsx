import { Button, Card, Popover, Space } from "antd";
import { Typography } from "antd";
import { InfoCircleOutlined } from "@ant-design/icons";

import Marked from "../general/Marked";
import MdView from "../template/MdView";
import "./style.css";

const { Title } = Typography;

export interface IWordByDict {
  dictname: string;
  description?: string;
  word?: string;
  note?: string;
  anchor: string;
}
interface IWidgetWordCardByDict {
  data: IWordByDict;
  children?: React.ReactNode;
}
const WordCardByDictWidget = ({ data, children }: IWidgetWordCardByDict) => {
  return (
    <Card>
      <Space>
        <Title level={5} id={data.anchor}>
          {data.dictname}
        </Title>
        {data.description ? (
          <Popover
            overlayStyle={{ maxWidth: 600 }}
            content={<Marked text={data.description} />}
            placement="bottom"
          >
            <Button type="link" icon={<InfoCircleOutlined />} />
          </Popover>
        ) : undefined}
      </Space>
      <div className="dict_content">
        <MdView html={data.note} />
      </div>
      {children}
    </Card>
  );
};

export default WordCardByDictWidget;
