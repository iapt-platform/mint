import { Button, Card, Collapse, Modal, Popover, Space } from "antd";
import { Typography } from "antd";
import { useState } from "react";
import { CloseOutlined } from "@ant-design/icons";

import { TDisplayStyle } from "./Article";
import Video from "../general/Video";
import { VideoIcon } from "../../assets/icon";
import { IAttachmentResponse } from "../api/Attachments";
import { get } from "../../request";

const { Text } = Typography;

const getUrl = async (fileId: string) => {
  const url = `/v2/attachment/${fileId}`;
  console.info("url", url);
  const res = await get<IAttachmentResponse>(url);
  if (res.ok) {
    return res.data.url;
  } else {
    return "";
  }
};

const getLink = async ({ url, id, type, title }: IVideoCtl) => {
  let link = url;
  if (!link && id) {
    const res = await getUrl(id);
    link = res;
  }
  return link;
};

interface IVideoCtl {
  url?: string;
  id?: string;
  type?: string;
  title?: React.ReactNode;
  style?: TDisplayStyle;
  _style?: TDisplayStyle;
}

const VideoPopover = ({ url, id, type, title }: IVideoCtl) => {
  const [popOpen, setPopOpen] = useState(false);
  console.debug("popOpen", popOpen);
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
        <div style={{ width: 600, height: 480 }}>
          <Video fileId={id} src={url} type={type} />
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

const VideoModal = ({ url, id, type, title }: IVideoCtl) => {
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

  return (
    <>
      <Typography.Link
        onClick={async (event: React.MouseEvent<HTMLElement, MouseEvent>) => {
          if (event.ctrlKey || event.metaKey) {
            const link = await getLink({ url: url, id: id });
            window.open(link, "_blank");
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
        width={800}
        destroyOnClose
        style={{ maxWidth: "90%", top: 20, height: 700 }}
        title={
          <div
            style={{
              display: "flex",
              justifyContent: "space-between",
              marginRight: 30,
            }}
          >
            <Text>{title}</Text>
          </div>
        }
        open={isModalOpen}
        onOk={handleOk}
        onCancel={handleCancel}
        footer={[]}
      >
        <div style={{ height: 550 }}>
          <Video fileId={id} src={url} type={type} />
        </div>
      </Modal>
    </>
  );
};

export const VideoCtl = ({
  url,
  id,
  type,
  title,
  style = "modal",
  _style,
}: IVideoCtl) => {
  const curStyle = _style ? _style : style;

  const VideoCard = () => {
    return (
      <Card title={title} bodyStyle={{ width: 550, height: 420 }}>
        <Video fileId={id} src={url} type={type} />
      </Card>
    );
  };

  const VideoWindow = () => {
    return (
      <div style={{ width: 550, height: 420 }}>
        <Video fileId={id} src={url} type={type} />
      </div>
    );
  };

  const VideoToggle = () => {
    return (
      <Collapse bordered={false}>
        <Collapse.Panel header={title} key="parent2">
          <Video fileId={id} src={url} type={type} />
        </Collapse.Panel>
      </Collapse>
    );
  };

  const VideoLink = () => {
    return (
      <Typography.Link
        onClick={async () => {
          const link = await getLink({ url: url, id: id });
          window.open(link, "_blank");
        }}
      >
        <Space>
          <VideoIcon />
          {title}
        </Space>
      </Typography.Link>
    );
  };

  return curStyle === "modal" ? (
    <VideoModal url={url} id={id} type={type} title={title} />
  ) : curStyle === "card" ? (
    <VideoCard />
  ) : curStyle === "window" ? (
    <VideoWindow />
  ) : curStyle === "toggle" ? (
    <VideoToggle />
  ) : curStyle === "link" ? (
    <VideoLink />
  ) : curStyle === "popover" ? (
    <VideoPopover url={url} id={id} type={type} title={title} />
  ) : (
    <></>
  );
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
