import { useEffect, useState } from "react";
import { Divider, Input, Modal, Select, Space, Tabs, Typography } from "antd";

import { ArticleCtl, TDisplayStyle } from "../Article";
import { ArticleType } from "../../article/Article";
import { useIntl } from "react-intl";
const { TextArea } = Input;
const { Paragraph } = Typography;

interface IWidget {
  type?: ArticleType;
  id?: string;
  title?: string;
  style?: TDisplayStyle;
  onSelect?: Function;
  onCancel?: Function;
}
const ArticleTplWidget = ({
  type,
  id,
  title = "title",
  style = "modal",
}: IWidget) => {
  const intl = useIntl(); //i18n
  const [titleText, setTitleText] = useState(title);
  const [styleText, setStyleText] = useState(style);
  const [typeText, setTypeText] = useState(type);
  const [idText, setIdText] = useState(id);
  const [tplText, setTplText] = useState("");

  const ids = id?.split("_");
  const id1 = ids ? ids[0] : undefined;
  const channels = ids
    ? ids.length > 1
      ? ids?.slice(1)
      : undefined
    : undefined;

  useEffect(() => {
    setTitleText(title);
  }, [title]);
  useEffect(() => {
    let tplText = `{{article|
type=${typeText}|
id=${idText}|
title=${titleText}|
style=${styleText}`;

    tplText += channels ? `channel=${channels}` : "";
    tplText += "}}";

    setTplText(tplText);
  }, [titleText, styleText, type, id1, channels, typeText, idText]);
  return (
    <>
      <Space direction="vertical" style={{ width: 500 }}>
        <Space style={{ width: 500 }}>
          {"标题："}
          <Input
            width={400}
            value={titleText}
            placeholder="Title"
            onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
              setTitleText(event.target.value);
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
          <Input
            disabled={id ? true : false}
            defaultValue={id}
            width={400}
            value={idText}
            placeholder="Title"
            onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
              setIdText(event.target.value);
            }}
          />
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
            options={[
              { value: "modal", label: "对话框" },
              { value: "card", label: "卡片" },
            ]}
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
                  title={titleText}
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
  type?: ArticleType;
  id?: string;
  title?: string;
  style?: TDisplayStyle;
  trigger?: JSX.Element;
}
export const ArticleTplModal = ({
  type,
  id,
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
        <ArticleTplWidget type={type} id={id} title={title} style={style} />
      </Modal>
    </>
  );
};

export default ArticleTplWidget;
