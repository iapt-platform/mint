import { Col, Progress, Row, Space, Tabs } from "antd";
import { Typography } from "antd";
import { LikeOutlined, EyeOutlined } from "@ant-design/icons";
import { ChannelInfoProps } from "../api/Channel";
import ChannelListItem from "../channel/ChannelListItem";
import TimeShow from "../general/TimeShow";
import { useIntl } from "react-intl";
import { Link } from "react-router-dom";

const { Text } = Typography;

export interface IChapterChannelData {
  channel: ChannelInfoProps;

  progress: number;
  hit: number;
  like: number;
  updatedAt: string;
}
interface IWidgetChapterInChannel {
  data: IChapterChannelData[];
  book: number;
  para: number;
}
const Widget = ({ data, book, para }: IWidgetChapterInChannel) => {
  const intl = useIntl(); //i18n
  function getTab(type: string): JSX.Element[] {
    const output = data.map((item, id) => {
      if (item.channel.channelType === type) {
        return (
          <div key={id}>
            <Row>
              <Col span={5}>
                <Link
                  to={`/article/chapter/${book}-${para}_${item.channel.channelId}`}
                  target="_blank"
                >
                  <ChannelListItem data={item.channel} />
                </Link>
              </Col>
              <Col span={5}>
                <Progress percent={item.progress} size="small" />
              </Col>
              <Col span={8}></Col>
            </Row>

            <Text type="secondary">
              <Space style={{ paddingLeft: "2em" }}>
                <EyeOutlined />
                {item.hit} | <LikeOutlined />
                {item.like} |
                <TimeShow time={item.updatedAt} title={item.updatedAt} />
              </Space>
            </Text>
          </div>
        );
      } else {
        return <></>;
      }
    });
    return output;
  }

  const items = [
    {
      label: intl.formatMessage({ id: "channel.type.translation.label" }),
      key: "translation",
      children: getTab("translation"),
    },
    {
      label: intl.formatMessage({ id: "channel.type.nissaya.label" }),
      key: "nissaya",
      children: getTab("nissaya"),
    },
    {
      label: intl.formatMessage({ id: "channel.type.commentary.label" }),
      key: "commentary",
      children: getTab("commentary"),
    },
    {
      label: intl.formatMessage({ id: "channel.type.original.label" }),
      key: "original",
      children: getTab("original"),
    },
  ];
  return <Tabs items={items} />;
};

export default Widget;
