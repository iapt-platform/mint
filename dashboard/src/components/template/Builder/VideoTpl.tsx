import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import {
  Button,
  Divider,
  Input,
  Modal,
  Select,
  Space,
  Tabs,
  Typography,
} from "antd";
import { FolderOpenOutlined } from "@ant-design/icons";

import { TDisplayStyle } from "../Article";
import { VideoCtl } from "../Video";
import AttachmentDialog from "../../attachment/AttachmentDialog";
import { IAttachmentRequest } from "../../api/Attachments";

const { TextArea } = Input;
const { Paragraph } = Typography;

interface IWidget {
  url?: string;
  title?: string;
  style?: TDisplayStyle;
  onSelect?: Function;
  onCancel?: Function;
}
const VideoTplWidget = ({ url, title, style = "modal" }: IWidget) => {
  const intl = useIntl(); //i18n
  const [currTitle, setCurrTitle] = useState(title);

  const [styleText, setStyleText] = useState(style);

  const [urlText, setUrlText] = useState(url);
  const [tplText, setTplText] = useState("");

  useEffect(() => {
    setCurrTitle(title);
  }, [title]);
  useEffect(() => {
    let tplText = `{{v|\n`;
    tplText += `url=${urlText}|\n`;
    tplText += `title=${currTitle}|\n`;
    tplText += `style=${styleText}`;
    tplText += "}}";

    setTplText(tplText);
  }, [currTitle, styleText, urlText]);

  return (
    <>
      <Space direction="vertical" style={{ width: 500 }}>
        <AttachmentDialog
          trigger={<Button icon={<FolderOpenOutlined />}>网盘</Button>}
          onSelect={(value: IAttachmentRequest) => {
            console.debug("VideoTpl onSelect", value);
            setCurrTitle(value.title);
            setUrlText(value.url);
          }}
        />
        <Space style={{ width: 500 }}>
          {"标题："}
          <Input
            width={400}
            value={currTitle}
            placeholder="Title"
            onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
              setCurrTitle(event.target.value);
            }}
          />
        </Space>
        <Space style={{ width: 500 }}>
          {"Url"}
          <Space>
            <Input
              defaultValue={url}
              width={400}
              value={urlText}
              placeholder="Url"
              onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
                setUrlText(event.target.value);
              }}
            />
          </Space>
        </Space>

        <Space>
          {"显示为："}
          <Select
            defaultValue={style}
            style={{ width: 120 }}
            onChange={(value: string) => {
              console.log(`selected ${value}`);
              setStyleText(value as TDisplayStyle);
            }}
            options={["window", "modal", "card", "toggle", "link"].map(
              (item) => {
                return { value: item, label: item };
              }
            )}
          />
        </Space>
        <Tabs
          size="small"
          defaultActiveKey="preview"
          items={[
            {
              label: intl.formatMessage({
                id: "buttons.preview",
              }),
              key: "preview",
              children: (
                <VideoCtl url={urlText} title={currTitle} style={styleText} />
              ),
            },
            {
              label: `Code`,
              key: "code",
              children: (
                <TextArea
                  value={tplText}
                  rows={4}
                  placeholder="maxLength is 6"
                  maxLength={6}
                />
              ),
            },
          ]}
        />
        <Divider></Divider>
        <Paragraph copyable={{ text: tplText }}>复制到剪贴板</Paragraph>
      </Space>
    </>
  );
};

interface IModalWidget {
  url?: string;
  title?: string;
  style?: TDisplayStyle;
  trigger?: JSX.Element;
}
export const VideoTplModal = ({
  url,
  title,
  style = "modal",
  trigger,
}: IModalWidget) => {
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
      <span onClick={showModal}>{trigger}</span>
      <Modal
        width={"80%"}
        title="生成模版"
        open={isModalOpen}
        onOk={handleOk}
        onCancel={handleCancel}
      >
        <VideoTplWidget url={url} title={title} style={style} />
      </Modal>
    </>
  );
};

export default VideoTplWidget;
