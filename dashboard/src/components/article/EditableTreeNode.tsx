import { Button, Space, Typography } from "antd";
import { useState } from "react";
import { PlusOutlined } from "@ant-design/icons";
import { TreeNodeData } from "./EditableTree";
const { Text } = Typography;

interface IWidget {
  node: TreeNodeData;
  onAdd?: Function;
}
const EditableTreeNodeWidget = ({ node, onAdd }: IWidget) => {
  const [showNodeMenu, setShowNodeMenu] = useState(false);
  const [loading, setLoading] = useState(false);

  const title = node.deletedAt ? (
    <Text delete disabled>
      {node.title}
    </Text>
  ) : (
    <>{node.title}</>
  );
  const menu = (
    <Space style={{ visibility: showNodeMenu ? "visible" : "hidden" }}>
      <Button
        loading={loading}
        size="small"
        icon={<PlusOutlined />}
        type="text"
        onClick={async () => {
          if (typeof onAdd !== "undefined") {
            setLoading(true);
            const ok = await onAdd();
            setLoading(false);
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
      {title}
      {menu}
    </Space>
  );
};

export default EditableTreeNodeWidget;
