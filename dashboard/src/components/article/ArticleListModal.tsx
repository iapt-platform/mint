import { useState } from "react";
import { Modal } from "antd";

import ArticleList from "./ArticleList";

interface IWidget {
  studioName?: string;
  trigger?: React.ReactNode;
  multiple?: boolean;
  onSelect?: Function;
}
const ArticleListModalWidget = ({
  studioName,
  trigger = "Article",
  multiple = true,
  onSelect,
}: IWidget) => {
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
        title="文章列表"
        open={isModalOpen}
        onOk={handleOk}
        onCancel={handleCancel}
      >
        <ArticleList
          studioName={studioName}
          editable={false}
          multiple={multiple}
          onSelect={(id: string, title: string) => {
            if (typeof onSelect !== "undefined") {
              onSelect(id, title);
            }
            handleOk();
          }}
        />
      </Modal>
    </>
  );
};

export default ArticleListModalWidget;
