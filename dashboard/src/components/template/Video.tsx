import { Card, Collapse, Modal, Space } from "antd";
import { Typography } from "antd";
import { useState } from "react";

import { Link } from "react-router-dom";
import { TDisplayStyle } from "./Article";
import Video from "../general/Video";
import { VideoIcon } from "../../assets/icon";
import { useIntl } from "react-intl";

const { Text } = Typography;

interface IVideoCtl {
  url?: string;
  title?: React.ReactNode;
  style?: TDisplayStyle;
}

export const VideoCtl = ({ url, title, style = "modal" }: IVideoCtl) => {
  const intl = useIntl();
  const [isModalOpen, setIsModalOpen] = useState(false);
  const showModal = () => {
    setIsModalOpen(true);
  };

  const handleOk = () => {
    setIsModalOpen(false);
  };

  const handleCancel = () => {
    setIsModalOpen(false);
  };

  let output = <></>;
  let articleLink = url ? url : "";

  switch (style) {
    case "modal":
      output = (
        <>
          <Typography.Link
            onClick={(event: React.MouseEvent<HTMLElement, MouseEvent>) => {
              if (event.ctrlKey || event.metaKey) {
                window.open(articleLink, "_blank");
              } else {
                showModal();
              }
            }}
          >
            <Space>
              <VideoIcon />
              {title}
            </Space>
          </Typography.Link>
          <Modal
            width={"90%"}
            destroyOnClose
            style={{ maxWidth: 1000, top: 20, height: 700 }}
            title={
              <div
                style={{
                  display: "flex",
                  justifyContent: "space-between",
                  marginRight: 30,
                }}
              >
                <Text>{title}</Text>
                <Text>
                  <Link to={articleLink} target="_blank">
                    {intl.formatMessage({
                      id: "buttons.open.in.new.tab",
                    })}
                  </Link>
                </Text>
              </div>
            }
            open={isModalOpen}
            onOk={handleOk}
            onCancel={handleCancel}
            footer={[]}
          >
            <div style={{ height: 550 }}>
              <Video src={url} />
            </div>
          </Modal>
        </>
      );
      break;
    case "card":
      output = (
        <Card title={title}>
          <Video src={url} />
        </Card>
      );
      break;
    case "toggle":
      output = (
        <Collapse bordered={false}>
          <Collapse.Panel header={title} key="parent2">
            <Video src={url} />
          </Collapse.Panel>
        </Collapse>
      );
      break;
    case "link":
      output = (
        <Link to={articleLink} target="_blank">
          <Space>
            <VideoIcon />
            {title}
          </Space>
        </Link>
      );
      break;
    default:
      break;
  }
  return output;
};

interface IWidget {
  props: string;
  children?: React.ReactNode;
}
const VideoWidget = ({ props, children }: IWidget) => {
  const prop = JSON.parse(atob(props)) as IVideoCtl;
  return <VideoCtl {...prop} />;
};

export default VideoWidget;
