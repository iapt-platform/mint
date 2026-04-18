import { Button, Card, Collapse, Modal, Popover, Space } from "antd";
import { Typography } from "antd";
import { useState } from "react";
import { CloseOutlined } from "@ant-design/icons";

import Video from "../video/Video";
import { VideoIcon } from "../../assets/icon";
import type { IAttachmentResponse } from "../../api/Attachments";
import { get } from "../../request";
import type { TDisplayStyle } from "../../types/template";

const { Text } = Typography;

const getUrl = async (fileId: string) => {
  const url = `/v2/attachment/${fileId}`;
  const res = await get<IAttachmentResponse>(url);
  return res.ok ? res.data.url : "";
};

const getLink = async ({ url, id }: IVideoCtl) => {
  let link = url;
  if (!link && id) {
    link = await getUrl(id);
  }
  return link;
};

interface IVideoCtl {
  url?: string;
  id?: string;
  type?: string;
  title?: React.ReactNode;
  style?: TDisplayStyle;
}

// ---- 所有子组件提取到顶层 ----

const VideoPopover = ({ url, id, type, title }: IVideoCtl) => {
  const [popOpen, setPopOpen] = useState(false);
  return (
    <Popover
      title={
        <div>
          {title}
          <Button
            type="link"
            icon={<CloseOutlined />}
            onClick={() => setPopOpen(false)}
          />
        </div>
      }
      content={
        <div style={{ width: 600, height: 480 }}>
          <Video fileId={id} src={url} type={type} />
        </div>
      }
      trigger="click"
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

  return (
    <>
      <Typography.Link
        onClick={async (e: React.MouseEvent<HTMLElement>) => {
          if (e.ctrlKey || e.metaKey) {
            const link = await getLink({ url, id });
            window.open(link, "_blank");
          } else {
            setIsModalOpen(true);
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
        onOk={() => setIsModalOpen(false)}
        onCancel={() => setIsModalOpen(false)}
        footer={[]}
      >
        <div style={{ height: 550 }}>
          <Video fileId={id} src={url} type={type} />
        </div>
      </Modal>
    </>
  );
};

const VideoCard = ({ url, id, type, title }: IVideoCtl) => (
  <Card title={title} bodyStyle={{ width: 550, height: 420 }}>
    <Video fileId={id} src={url} type={type} />
  </Card>
);

const VideoWindow = ({ url, id, type }: IVideoCtl) => (
  <div style={{ width: 550, height: 420 }}>
    <Video fileId={id} src={url} type={type} />
  </div>
);

const VideoToggle = ({ url, id, type, title }: IVideoCtl) => (
  <Collapse bordered={false}>
    <Collapse.Panel header={title} key="parent2">
      <Video fileId={id} src={url} type={type} />
    </Collapse.Panel>
  </Collapse>
);

const VideoLink = ({ url, id, title }: IVideoCtl) => (
  <Typography.Link
    onClick={async () => {
      const link = await getLink({ url, id });
      window.open(link, "_blank");
    }}
  >
    <Space>
      <VideoIcon />
      {title}
    </Space>
  </Typography.Link>
);

// ---- VideoCtl 主组件 ----

export const VideoCtl = ({
  url,
  id,
  type,
  title,
  style = "modal",
}: IVideoCtl) => {
  const props = { url, id, type, title };

  switch (style) {
    case "modal":
      return <VideoModal {...props} />;
    case "card":
      return <VideoCard {...props} />;
    case "window":
      return <VideoWindow {...props} />;
    case "toggle":
      return <VideoToggle {...props} />;
    case "link":
      return <VideoLink {...props} />;
    case "popover":
      return <VideoPopover {...props} />;
    default:
      return <></>;
  }
};

// ---- Widget 入口 ----

interface IWidget {
  props: string;
  children?: React.ReactNode;
}

const VideoWidget = ({ props }: IWidget) => {
  const prop = JSON.parse(atob(props)) as IVideoCtl;
  return <VideoCtl {...prop} />;
};

export default VideoWidget;
