import { useEffect, useState } from "react";
import { Modal, Tabs } from "antd";

import ArticleTpl from "./ArticleTpl";
import VideoTpl from "./VideoTpl";
import { ArticleType } from "../../article/Article";

interface IWidget {
  trigger?: React.ReactNode;
  open?: boolean;
  tpl?: ArticleType;
  articleId?: string;
  title?: string;
  onClose?: () => void;
}
const TplBuilderWidget = ({
  trigger,
  open = false,
  tpl,
  articleId,
  title,
  onClose,
}: IWidget) => {
  const [isModalOpen, setIsModalOpen] = useState(open);

  useEffect(() => setIsModalOpen(open), [open]);
  useEffect(() => {}, [tpl]);

  const showModal = () => {
    setIsModalOpen(true);
  };

  const handleCancel = () => {
    if (onClose) {
      onClose();
    } else {
      setIsModalOpen(false);
    }
  };

  return (
    <>
      <span onClick={showModal}>{trigger}</span>
      <Modal
        style={{ top: 20 }}
        width={900}
        footer={false}
        title="template builder"
        open={isModalOpen}
        onCancel={handleCancel}
      >
        <Tabs
          tabPosition="left"
          defaultActiveKey="article"
          items={[
            {
              label: "article",
              key: "article",
              children: <ArticleTpl articleId={articleId} type={tpl} />,
            }, // 务必填写 key
            { label: "video", key: "video", children: <VideoTpl /> },
          ]}
        />
      </Modal>
    </>
  );
};

export default TplBuilderWidget;
