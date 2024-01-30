import { useEffect, useState } from "react";
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

import { ArticleCtl, TDisplayStyle } from "../Article";
import { ArticleType } from "../../article/Article";
import { useIntl } from "react-intl";
import ArticleListModal from "../../article/ArticleListModal";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";
import ChannelTableModal from "../../channel/ChannelTableModal";
import { IChannel } from "../../channel/Channel";
const { TextArea } = Input;
const { Paragraph } = Typography;

interface IWidget {
  type?: ArticleType;
  id?: string;
  title?: string;
  style?: TDisplayStyle;
  channel?: string;
  onSelect?: Function;
  onCancel?: Function;
}
const ArticleTplWidget = ({
  type,
  id,
  channel,
  title,
  style = "modal",
}: IWidget) => {
  const intl = useIntl(); //i18n
  const [currTitle, setCurrTitle] = useState(title);
  const [currChannel, setCurrChannel] = useState(channel);
  const [styleText, setStyleText] = useState(style);
  const [typeText, setTypeText] = useState(type);
  const [idText, setIdText] = useState(id);
  const [tplText, setTplText] = useState("");
  const user = useAppSelector(currentUser);

  const ids = id?.split("_");
  const id1 = ids ? ids[0] : undefined;
  const channels = ids
    ? ids.length > 1
      ? ids?.slice(1)
      : undefined
    : undefined;

  useEffect(() => {
    setCurrTitle(title);
  }, [title]);
  useEffect(() => {
    let tplText = `{{article|\n`;
    tplText += `type=${typeText}|\n`;
    tplText += `id=${idText}|\n`;
    tplText += `title=${currTitle}|\n`;
    tplText += currChannel ? `channel=${currChannel}|\n` : "";
    tplText += `style=${styleText}`;

    tplText += channels ? `channel=${channels}` : "";
    tplText += "}}";

    setTplText(tplText);
  }, [currTitle, styleText, type, id1, channels, typeText, idText]);
  return (
    <>
      <Space direction="vertical" style={{ width: 500 }}>
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
        <Space>
          {"类别："}
          <Select
            disabled={type ? true : false}
            defaultValue={type}
            style={{ width: 120 }}
            onChange={(value: string) => {
              console.log(`selected ${value}`);
              setTypeText(value as ArticleType);
            }}
            options={["article", "chapter", "para"].map((item) => {
              return { value: item, label: item };
            })}
          />
        </Space>
        <Space style={{ width: 500 }}>
          {"id："}
          <Space>
            <Input
              disabled={id ? true : false}
              defaultValue={id}
              width={400}
              value={idText}
              placeholder="Id"
              onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
                setIdText(event.target.value);
              }}
            />
            {typeText === "article" ? (
              <ArticleListModal
                studioName={user?.realName}
                trigger={<Button icon={<FolderOpenOutlined />} type="text" />}
                multiple={false}
                onSelect={(id: string, title: string) => {
                  setIdText(id);
                  setCurrTitle(title);
                }}
              />
            ) : undefined}
          </Space>
        </Space>
        <Space style={{ width: 500 }}>
          {"channel："}
          <Space>
            <Input
              defaultValue={channel}
              width={400}
              value={currChannel}
              placeholder="channel"
              onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
                setCurrChannel(event.target.value);
              }}
            />
            <ChannelTableModal
              trigger={<Button icon={<FolderOpenOutlined />} type="text" />}
              onSelect={(channel: IChannel) => {
                setCurrChannel(channel.id);
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
            options={["modal", "card", "toggle", "link"].map((item) => {
              return { value: item, label: item };
            })}
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
                <ArticleCtl
                  type={typeText}
                  id={idText}
                  title={currTitle}
                  style={styleText}
                />
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
  open?: boolean;
  type?: ArticleType;
  id?: string;
  title?: string;
  style?: TDisplayStyle;
  trigger?: JSX.Element;
  onOpenChange?: Function;
}
export const ArticleTplModal = ({
  open = false,
  type,
  id,
  title,
  style = "modal",
  trigger,
  onOpenChange,
}: IModalWidget) => {
  const [isModalOpen, setIsModalOpen] = useState(open);

  useEffect(() => setIsModalOpen(open), [open]);

  const showModal = () => {
    setIsModalOpen(true);
  };

  const handleOk = () => {
    setIsModalOpen(false);
  };

  const handleCancel = () => {
    setIsModalOpen(false);
    if (typeof onOpenChange !== "undefined") {
      onOpenChange(false);
    }
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
        destroyOnClose
      >
        <ArticleTplWidget type={type} id={id} title={title} style={style} />
      </Modal>
    </>
  );
};

export default ArticleTplWidget;
