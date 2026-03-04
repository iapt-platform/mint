import { Descriptions, Modal } from "antd";
import { LoadingOutlined } from "@ant-design/icons";
import { useArticle } from "./hooks/useArticle";

interface IWidget {
  open?: boolean;
  articleId?: string;
  onClose?: () => void;
}

const WordCount = ({ open = false, articleId, onClose }: IWidget) => {
  const { data, loading } = useArticle(articleId, {
    format: "text",
    origin: true,
  });

  const wordAll = data?.html?.length ?? 0;

  return (
    <Modal
      destroyOnHidden={true}
      width={700}
      title="字数统计"
      open={open}
      footer={false}
      onOk={onClose}
      onCancel={onClose}
    >
      {loading ? (
        <LoadingOutlined />
      ) : (
        <Descriptions title="字数">
          <Descriptions.Item label="全部字符">{wordAll}</Descriptions.Item>
        </Descriptions>
      )}
    </Modal>
  );
};

export default WordCount;
