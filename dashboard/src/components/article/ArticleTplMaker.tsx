import { useEffect, useState } from "react";
import { Input, Modal, Select, Space, Typography } from "antd";

import { TDisplayStyle } from "../template/Article";
const { TextArea } = Input;
const { Paragraph } = Typography;
interface IWidget {
  type?: string;
  id?: string;
  title?: string;
  style?: TDisplayStyle;
  trigger?: JSX.Element;
  onSelect?: Function;
  onCancel?: Function;
}
const Widget = ({
  type,
  id,
  title,
  style = "modal",
  trigger,
  onSelect,
  onCancel,
}: IWidget) => {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [titleText, setTitleText] = useState(title);
  const [styleText, setStyleText] = useState(style);
  const [tplText, setTplText] = useState("");

  const ids = id?.split("_");
  const id1 = ids ? ids[0] : undefined;
  const channels = ids
    ? ids.length > 1
      ? ids?.slice(1)
      : undefined
    : undefined;

  const showModal = () => {
    setIsModalOpen(true);
  };

  const handleOk = () => {
    setIsModalOpen(false);
  };

  const handleCancel = () => {
    setIsModalOpen(false);
  };
  useEffect(() => {
    setTitleText(title);
  }, [title]);
  useEffect(() => {
    let tplText = `{{article|
type=${type}|
id=${id1}|
title=${titleText}|
style=${styleText}`;
    tplText += channels ? `channel=${channels}` : undefined;
    tplText += "}}";

    setTplText(tplText);
  }, [titleText, styleText, type, id1, channels]);
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
        <Space direction="vertical" style={{ width: 500 }}>
          <Space style={{ width: 500 }}>
            {"标题："}
            <Input
              width={400}
              value={titleText}
              placeholder="Basic usage"
              onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
                setTitleText(event.target.value);
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
          <div>
            <TextArea
              value={tplText}
              rows={4}
              placeholder="maxLength is 6"
              maxLength={6}
            />
            <Paragraph copyable={{ text: tplText }}>复制</Paragraph>
          </div>
        </Space>
      </Modal>
    </>
  );
};

export default Widget;
