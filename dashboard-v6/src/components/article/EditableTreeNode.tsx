import { Button, message, Space, Typography } from "antd";
import { useState } from "react";
import { PlusOutlined } from "@ant-design/icons";

import { TreeNodeData } from "./EditableTree";
const { Text } = Typography;

interface IWidget {
  node: TreeNodeData;
  onAdd?: Function;
  onTitleClick?: Function;
}
const EditableTreeNodeWidget = ({ node, onAdd, onTitleClick }: IWidget) => {
  const [showNodeMenu, setShowNodeMenu] = useState(false);
  const [loading, setLoading] = useState(false);

  const title = (
    <Text type={node.status === 10 ? "secondary" : undefined}>
      {node.title_text ? node.title_text : node.title}
    </Text>
  );

  const TitleText = () =>
    node.deletedAt ? (
      <Text delete disabled>
        {title}
      </Text>
    ) : (
      <Text
        onClick={(e: React.MouseEvent<HTMLElement, MouseEvent>) => {
          if (typeof onTitleClick !== "undefined") {
            onTitleClick(e);
          }
        }}
      >
        {title}
      </Text>
    );

  const Menu = () => (
    <Space style={{ visibility: showNodeMenu ? "visible" : "hidden" }}>
      <Button
        loading={loading}
        size="middle"
        icon={<PlusOutlined />}
        type="text"
        onClick={async () => {
          if (typeof onAdd !== "undefined") {
            setLoading(true);
            const ok = await onAdd();
            setLoading(false);
            if (!ok) {
              message.error("error");
            }
          }
        }}
      />
    </Space>
  );

  return (
    <Space
      onMouseEnter={() => setShowNodeMenu(true)}
      onMouseLeave={() => setShowNodeMenu(false)}
    >
      <TitleText />
      <Menu />
    </Space>
  );
};

export default EditableTreeNodeWidget;
