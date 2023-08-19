import { Card, Collapse, Modal } from "antd";
import { Typography } from "antd";
import { useState } from "react";
import Article, { ArticleType } from "../article/Article";

const { Link } = Typography;
export type TDisplayStyle = "modal" | "card" | "toggle";
interface IWidgetChapterCtl {
  type?: ArticleType;
  id?: string;
  channel?: string;
  title?: string;
  style?: TDisplayStyle;
}

export const ArticleCtl = ({
  type,
  id,
  channel,
  title,
  style = "modal",
}: IWidgetChapterCtl) => {
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
  const aTitle = title ? title : "chapter" + id;
  const article = (
    <Article
      active={true}
      type={type}
      articleId={id}
      channelId={channel}
      mode="read"
    />
  );
  let output = <></>;
  switch (style) {
    case "modal":
      output = (
        <>
          <Link onClick={showModal}>{aTitle}</Link>
          <Modal
            width={"80%"}
            style={{ maxWidth: 1000 }}
            title={aTitle}
            open={isModalOpen}
            onOk={handleOk}
            onCancel={handleCancel}
            footer={[]}
          >
            {article}
          </Modal>
        </>
      );
      break;
    case "card":
      output = <Card title={aTitle}>{article}</Card>;
      break;
    case "toggle":
      output = (
        <Collapse bordered={false}>
          <Collapse.Panel header={aTitle} key="parent2">
            {article}
          </Collapse.Panel>
        </Collapse>
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
const ArticleWidget = ({ props, children }: IWidget) => {
  const prop = JSON.parse(atob(props)) as IWidgetChapterCtl;
  return <ArticleCtl {...prop} />;
};

export default ArticleWidget;
