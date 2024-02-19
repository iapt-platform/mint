import { Button, Card, Collapse, Modal, Popover, Space } from "antd";
import { Typography } from "antd";
import { useEffect, useState } from "react";
import { CloseOutlined } from "@ant-design/icons";

import { Link } from "react-router-dom";
import { TDisplayStyle } from "./Article";
import Video from "../general/Video";
import { VideoIcon } from "../../assets/icon";
import { useIntl } from "react-intl";
import { IAttachmentResponse } from "../api/Attachments";
import { get } from "../../request";

const { Text } = Typography;

interface IVideoCtl {
  url?: string;
  id?: string;
  title?: React.ReactNode;
  style?: TDisplayStyle;
  _style?: TDisplayStyle;
}

export const VideoCtl = ({
  url,
  id,
  title,
  style = "modal",
  _style,
}: IVideoCtl) => {
  const intl = useIntl();
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [fetchUrl, setFetchUrl] = useState<string>();
  style = _style ? _style : style;
  useEffect(() => {
    if (id) {
      const url = `/v2/attachment/${id}`;
      console.info("url", url);
      get<IAttachmentResponse>(url).then((json) => {
        console.log(json);
        if (json.ok) {
          setFetchUrl(json.data.url);
        }
      });
    }
  }, [id]);

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
  let articleLink = url ? url : fetchUrl ? fetchUrl : "";

  const VideoModal = () => {
    return (
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
  };

  const VideoCard = () => {
    return (
      <Card title={title} bodyStyle={{ width: 550, height: 420 }}>
        <Video src={url} />
      </Card>
    );
  };

  const VideoWindow = () => {
    return (
      <div style={{ width: 550, height: 420 }}>
        <Video src={url} />
      </div>
    );
  };

  const VideoToggle = () => {
    return (
      <Collapse bordered={false}>
        <Collapse.Panel header={title} key="parent2">
          <Video src={url} />
        </Collapse.Panel>
      </Collapse>
    );
  };

  const VideoLink = () => {
    return (
      <Link to={articleLink} target="_blank">
        <Space>
          <VideoIcon />
          {title}
        </Space>
      </Link>
    );
  };

  const VideoPopover = () => {
    const [popOpen, setPopOpen] = useState(false);

    return (
      <Popover
        title={
          <div>
            {title}
            <Button
              type="link"
              icon={<CloseOutlined />}
              onClick={() => {
                setPopOpen(false);
              }}
            />
          </div>
        }
        content={
          <div style={{ width: 600, height: 350 }}>
            <Video src={url} />
          </div>
        }
        trigger={"click"}
        placement="bottom"
        open={popOpen}
      >
        <span onClick={() => setPopOpen(true)}>
          <VideoIcon />
          {title}
        </span>
      </Popover>
    );
  };
  switch (style) {
    case "modal":
      output = <VideoModal />;
      break;
    case "card":
      output = <VideoCard />;
      break;
    case "window":
      output = <VideoWindow />;
      break;
    case "toggle":
      output = <VideoToggle />;
      break;
    case "link":
      output = <VideoLink />;
      break;
    case "popover":
      output = <VideoPopover />;
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
