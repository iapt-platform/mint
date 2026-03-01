import { Button, message, Space, Typography } from "antd";
import { useState } from "react";
import { PlusOutlined } from "@ant-design/icons";

import type { TreeNodeData } from "./EditableTree";
const { Text } = Typography;

interface IWidget {
  node: TreeNodeData;
  onAdd?: () => Promise<boolean>;
  onTitleClick?: (e: React.MouseEvent<HTMLElement, MouseEvent>) => void;
}
const EditableTreeNodeWidget = ({ node, onAdd, onTitleClick }: IWidget) => {
  const [showNodeMenu, setShowNodeMenu] = useState(false);
  const [loading, setLoading] = useState(false);

  const title = (
    <Text type={node.status === 10 ? "secondary" : undefined}>
      {node.title_text ? node.title_text : node.title}
    </Text>
  );

  return (
    <Space
      onMouseEnter={() => setShowNodeMenu(true)}
      onMouseLeave={() => setShowNodeMenu(false)}
    >
      {node.deletedAt ? (
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
      )}

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
    </Space>
  );
};

export default EditableTreeNodeWidget;
