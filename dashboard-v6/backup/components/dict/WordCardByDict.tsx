import { Button, Card, Popover, Space, Tabs } from "antd";
import { Typography } from "antd";
import { InfoCircleOutlined } from "@ant-design/icons";

import Marked from "../general/Marked";
import MdView from "../template/MdView";
import "./style.css";
import DictInfoCopyRef from "./DictInfoCopyRef";

const { Title, Text } = Typography;

export interface IWordByDict {
  dictname: string;
  description?: string;
  meta?: IDictInfo;
  word?: string;
  note?: string;
  anchor: string;
}
export interface IDictInfo {
  author: string;
  publisher: string;
  published?: string;
  url: string;
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
        <Popover
          overlayStyle={{ maxWidth: 600 }}
          content={
            <div>
              <Tabs
                size="small"
                style={{ width: 600 }}
                items={[
                  {
                    label: "详情",
                    key: "info",
                    children: (
                      <div>
                        <div>
                          <Text strong>{data.dictname}</Text>
                        </div>
                        <div>
                          <Text type="secondary">Author:</Text>
                          <Text>{data.meta?.author}</Text>
                        </div>
                        <div>
                          <Text type="secondary">Publish:</Text>
                          <Text>{data.meta?.publisher}</Text>
                        </div>
                        <div>
                          <Text type="secondary">At:</Text>
                          <Text>{data.meta?.published}</Text>
                        </div>
                        <Marked text={data.description} />
                      </div>
                    ),
                  },
                  {
                    label: "复制引用信息",
                    key: "reference",
                    children: <DictInfoCopyRef data={data} />,
                  },
                ]}
              />
            </div>
          }
          placement="bottom"
        >
          <Button type="link" icon={<InfoCircleOutlined />} />
        </Popover>
      </Space>
      <div className="dict_content">
        <MdView html={data.note} />
      </div>
      {children}
    </Card>
  );
};

export default WordCardByDictWidget;
