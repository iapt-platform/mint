import { Col, List, Modal, Progress, Row, Space, Typography } from "antd";

import ChannelListItem from "../channel/ChannelListItem";
import { IChapterChannelData } from "./ChapterInChannel";
import { LikeOutlined, EyeOutlined } from "@ant-design/icons";
import { useState } from "react";
import TimeShow from "../general/TimeShow";

const { Text } = Typography;

/**
 * 章节中的版本选择对话框
 * @returns
 */
interface IWidget {
  trigger?: JSX.Element | string;
  channels?: IChapterChannelData[];
  currChannel?: string;
  onSelect?: Function;
}
const ChapterChannelSelectWidget = ({
  trigger,
  channels,
  currChannel,
  onSelect,
}: IWidget) => {
  const [open, setOpen] = useState(false);

  const handleCancel = () => {
    setOpen(false);
  };

  return (
    <div>
      <div
        onClick={() => {
          setOpen(true);
        }}
      >
        {trigger}
      </div>
      <Modal
        title="版本选择"
        open={open}
        onCancel={handleCancel}
        onOk={handleCancel}
      >
        <List
          style={{ maxWidth: 500 }}
          itemLayout="vertical"
          size="small"
          dataSource={channels}
          pagination={
            currChannel
              ? undefined
              : {
                  showQuickJumper: false,
                  showSizeChanger: false,
                  pageSize: 5,
                  total: channels?.length,
                  position: "bottom",
                  showTotal: (total) => {
                    return `结果: ${total}`;
                  },
                }
          }
          renderItem={(item, id) => (
            <List.Item key={id}>
              <Row>
                <Col span={12}>
                  <div
                    onClick={() => {
                      if (typeof onSelect !== "undefined") {
                        onSelect(item.channel.id);
                      }
                    }}
                  >
                    <ChannelListItem
                      channel={item.channel}
                      studio={item.studio}
                    />
                  </div>
                </Col>
                <Col span={12}>
                  <Progress percent={item.progress} size="small" />
                </Col>
              </Row>

              <Text type="secondary">
                <Space style={{ paddingLeft: "2em" }}>
                  <EyeOutlined />
                  {item.hit} | <LikeOutlined />
                  {item.like} |
                  <TimeShow updatedAt={item.updatedAt} />
                </Space>
              </Text>
            </List.Item>
          )}
        />
      </Modal>
    </div>
  );
};

export default ChapterChannelSelectWidget;
