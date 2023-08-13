import { useState } from "react";
import { Col, Modal, Row, Tree } from "antd";
import { Key } from "antd/lib/table/interface";
import ArticleTpl from "./ArticleTpl";

interface DataNode {
  title: React.ReactNode;
  key: string;
  isLeaf?: boolean;
  children?: DataNode[];
}

interface tplListNode {
  name: string;
  component?: React.ReactNode;
}

interface IWidget {
  trigger?: React.ReactNode;
}
const TplBuilderWidget = ({ trigger }: IWidget) => {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [template, setTemplate] =
    useState<React.ReactNode>("在左侧列表选择一个模版");

  const tplList: tplListNode[] = [
    { name: "article", component: <ArticleTpl /> },
    { name: "note" },
    { name: "term" },
  ];
  const treeData: DataNode[] = tplList.map((item) => {
    return { title: item.name, key: item.name };
  });

  const showModal = () => {
    setIsModalOpen(true);
  };

  const handleCancel = () => {
    setIsModalOpen(false);
  };

  return (
    <>
      <span onClick={showModal}>{trigger}</span>
      <Modal
        width={700}
        footer={false}
        title="template builder"
        open={isModalOpen}
        onCancel={handleCancel}
      >
        <Row>
          <Col span={8}>
            <Tree
              treeData={treeData}
              onSelect={(selectedKeys: Key[]) => {
                if (selectedKeys.length > 0) {
                  const tpl = tplList.find(
                    (value) => value.name === selectedKeys[0]
                  )?.component;
                  setTemplate(tpl);
                }
              }}
            />
          </Col>
          <Col span={16}>{template}</Col>
        </Row>
      </Modal>
    </>
  );
};

export default TplBuilderWidget;
