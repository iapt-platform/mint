import { Typography, Divider, Button } from "antd";
import { ReloadOutlined } from "@ant-design/icons";

import MdView from "../template/MdView";
import TocPath, { ITocPathNode } from "../corpus/TocPath";

const { Paragraph, Title, Text } = Typography;

export interface IWidgetArticleData {
  id?: string;
  title?: string;
  subTitle?: string;
  summary?: string;
  content?: string;
  html?: string;
  path?: ITocPathNode[];
  created_at?: string;
  updated_at?: string;
  channels?: string[];
}

const Widget = ({
  id,
  title = "",
  subTitle,
  summary,
  content,
  html,
  path = [],
  created_at,
  updated_at,
  channels,
}: IWidgetArticleData) => {
  console.log("path", path);
  return (
    <>
      <Button shape="round" size="small" icon={<ReloadOutlined />}>
        刷新
      </Button>
      <div>
        <TocPath data={path} channel={channels} />

        <Title level={4}>
          <div
            dangerouslySetInnerHTML={{
              __html: title ? title : "",
            }}
          ></div>
        </Title>
        <Text type="secondary">{subTitle}</Text>
        <Paragraph ellipsis={{ rows: 2, expandable: true, symbol: "more" }}>
          {summary}
        </Paragraph>
        <Divider />
      </div>
      <div>
        <MdView html={html ? html : content} />
      </div>
    </>
  );
};

export default Widget;
