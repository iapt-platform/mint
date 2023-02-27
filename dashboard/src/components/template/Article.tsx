import { Modal } from "antd";
import { Typography } from "antd";
import { useState } from "react";
import Article, { ArticleType } from "../article/Article";

const { Link } = Typography;
export type TDisplayStyle = "modal" | "card";
interface IWidgetChapterCtl {
  type?: ArticleType;
  id?: string;
  channel?: string;
  title?: string;
  style?: TDisplayStyle;
}

const ArticleCtl = ({
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
  return (
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
