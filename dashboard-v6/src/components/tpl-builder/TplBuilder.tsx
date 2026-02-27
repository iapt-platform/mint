import { useEffect, useState } from "react";
import { Modal, Tabs } from "antd";
import type { ArticleType } from "../../api/Article";
import { ArticleTplMock } from "./ArticleTpl";
import { VideoTplMock } from "./VideoTpl";

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
  onClose,
}: IWidget) => {
  const [isModalOpen, setIsModalOpen] = useState(open);

  useEffect(() => {
    setIsModalOpen(open);
  }, [open]);

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
              children: <ArticleTplMock articleId={articleId} type={tpl} />,
            }, // 务必填写 key
            { label: "video", key: "video", children: <VideoTplMock /> },
          ]}
        />
      </Modal>
    </>
  );
};

export default TplBuilderWidget;
