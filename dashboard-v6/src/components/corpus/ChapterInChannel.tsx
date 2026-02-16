import { Button, Col, List, Modal, Row, Space, Tabs } from "antd";
import { Typography } from "antd";
import { LikeOutlined, EyeOutlined } from "@ant-design/icons";
import { TinyLine } from "@ant-design/plots";

import { IChannelApiData } from "../api/Channel";
import ChannelListItem from "../channel/ChannelListItem";
import TimeShow from "../general/TimeShow";
import { useIntl } from "react-intl";
import { Link, useSearchParams } from "react-router-dom";
import { IStudio } from "../auth/Studio";
import { useState } from "react";
import { ProgressOutlinedIcon } from "../../assets/icon";
import { ArticleMode } from "../article/Article";

const { Text } = Typography;

export interface IChapterChannelData {
  channel: IChannelApiData;
  studio: IStudio;
  progress: number;
  progressLine?: number[];
  hit: number;
  like: number;
  updatedAt: string;
}
interface IWidgetChapterInChannel {
  data: IChapterChannelData[];
  book: number;
  para: number;
  channelId?: string[];
  openTarget?: React.HTMLAttributeAnchorTarget;
}
const ChapterInChannelWidget = ({
  data,
  book,
  para,
  channelId,
  openTarget = "_blank",
}: IWidgetChapterInChannel) => {
  const intl = useIntl(); //i18n
  const [searchParams, setSearchParams] = useSearchParams();
  const [open, setOpen] = useState(false);
  const ChannelList = (channels: IChapterChannelData[]): JSX.Element => {
    return channels.length ? (
      <List
        style={{ maxWidth: 500 }}
        itemLayout="vertical"
        size="small"
        dataSource={channels}
        pagination={
          channelId
            ? undefined
            : {
                showQuickJumper: false,
                showSizeChanger: false,
                pageSize: 5,
                total: channels.length,
                position: "bottom",
                showTotal: (total) => {
                  return `结果: ${total}`;
                },
              }
        }
        renderItem={(item, id) => {
          let url = `/article/chapter/${book}-${para}`;
          const currMode = searchParams.get("mode");
          url += currMode ? `?mode=${currMode}` : "?mode=read";
          url += item.channel.id ? `&channel=${item.channel.id}` : "";
          return (
            <List.Item key={id}>
              <Row>
                <Col span={12}>
                  <Link to={url}>
                    <ChannelListItem
                      channel={item.channel}
                      studio={item.studio}
                    />
                  </Link>
                </Col>
                <Col span={12}>
                  {item.progressLine ? (
                    <TinyLine
                      height={32}
                      width={150}
                      autoFit={false}
                      data={item.progressLine}
                      smooth={true}
                    />
                  ) : (
                    <></>
                  )}
                </Col>
              </Row>
              <div style={{ display: "flex", justifyContent: "space-between" }}>
                <Text type="secondary">
                  <Space style={{ paddingLeft: "2em" }}>
                    <EyeOutlined />
                    {item.hit} | <LikeOutlined />
                    {item.like} |
                    <TimeShow updatedAt={item.updatedAt} /> |
                    <ProgressOutlinedIcon />
                    {`${item.progress}%`}
                  </Space>
                </Text>
              </div>
            </List.Item>
          );
        }}
      />
    ) : (
      <></>
    );
  };

  const handleCancel = () => {
    setOpen(false);
  };

  if (typeof channelId !== "undefined") {
    const channelList = ChannelList(
      data.filter((item) => channelId.includes(item.channel.id))
    );
    return (
      <div>
        <div>{channelList}</div>
        <div>
          <Button
            type="link"
            onClick={() => {
              setOpen(true);
            }}
          >
            {intl.formatMessage({ id: "buttons.more" })}
          </Button>
        </div>
        <Modal
          title="版本选择"
          open={open}
          onCancel={handleCancel}
          onOk={handleCancel}
        >
          <div>{ChannelList(data)}</div>
        </Modal>
      </div>
    );
  } else {
    return (
      <Tabs
        items={[
          {
            label: intl.formatMessage({ id: "channel.type.translation.label" }),
            key: "translation",
            children: ChannelList(
              data.filter((item) => item.channel.type === "translation")
            ),
          },
          {
            label: intl.formatMessage({ id: "channel.type.nissaya.label" }),
            key: "nissaya",
            children: ChannelList(
              data.filter((item) => item.channel.type === "nissaya")
            ),
          },
          {
            label: intl.formatMessage({ id: "channel.type.commentary.label" }),
            key: "commentary",
            children: ChannelList(
              data.filter((item) => item.channel.type === "commentary")
            ),
          },
          {
            label: intl.formatMessage({ id: "channel.type.original.label" }),
            key: "original",
            children: ChannelList(
              data.filter((item) => item.channel.type === "original")
            ),
          },
        ]}
      />
    );
  }
};

export default ChapterInChannelWidget;
