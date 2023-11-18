import { Button, message, Space, Typography } from "antd";
import { useState } from "react";
import { PlusOutlined, EditOutlined } from "@ant-design/icons";

import { TreeNodeData } from "./EditableTree";
const { Text } = Typography;

interface IWidget {
  node: TreeNodeData;
  onAdd?: Function;
  onEdit?: Function;
  onTitleClick?: Function;
}
const EditableTreeNodeWidget = ({
  node,
  onAdd,
  onEdit,
  onTitleClick,
}: IWidget) => {
  const [showNodeMenu, setShowNodeMenu] = useState(false);
  const [loading, setLoading] = useState(false);

  const title = node.deletedAt ? (
    <Text delete disabled>
      {node.title_text ? node.title_text : node.title}
    </Text>
  ) : (
    <Text
      onClick={(e: React.MouseEvent<HTMLElement, MouseEvent>) => {
        if (typeof onTitleClick !== "undefined") {
          onTitleClick(e);
        }
      }}
    >
      {node.title_text ? node.title_text : node.title}
    </Text>
  );
  const menu = (
    <Space style={{ visibility: showNodeMenu ? "visible" : "hidden" }}>
      <Button
        size="middle"
        icon={<EditOutlined />}
        type="text"
        onClick={async () => {
          if (typeof onEdit !== "undefined") {
            onEdit();
          }
        }}
      />
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
      {title}
      {menu}
    </Space>
  );
};

export default EditableTreeNodeWidget;
