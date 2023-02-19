import { Modal } from "antd";
import { Typography } from "antd";
import { useState } from "react";
import Article, { ArticleType } from "../article/Article";

const { Link } = Typography;

interface IWidgetChapterCtl {
  type?: ArticleType;
  id?: string;
  channel?: string;
  text?: string;
}
const ArticleCtl = ({ type, id, channel, text }: IWidgetChapterCtl) => {
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
      <Link onClick={showModal}>{text ? text : "chapter" + id}</Link>
      <Modal
        width={"80%"}
        style={{ maxWidth: 1000 }}
        title="chapter"
        open={isModalOpen}
        onOk={handleOk}
        onCancel={handleCancel}
        footer={[]}
      >
        <Article
          active={true}
          type={type}
          articleId={id + (channel ? "_" + channel : "")}
          mode="read"
        />
      </Modal>
    </>
  );
};

interface IWidget {
  props: string;
  children?: React.ReactNode;
}
const Widget = ({ props, children }: IWidget) => {
  const prop = JSON.parse(atob(props)) as IWidgetChapterCtl;
  return <ArticleCtl {...prop} />;
};

export default Widget;
