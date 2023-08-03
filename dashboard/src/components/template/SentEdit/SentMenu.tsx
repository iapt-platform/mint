import { Badge, Button, Dropdown, Space } from "antd";
import { MoreOutlined } from "@ant-design/icons";
import type { MenuProps } from "antd";
import RelatedPara from "../../corpus/RelatedPara";

interface ISentMenu {
  book?: number;
  para?: number;
  loading?: boolean;
  onMagicDict?: Function;
  onMenuClick?: Function;
}
const SentMenuWidget = ({
  book,
  para,
  loading = false,
  onMagicDict,
  onMenuClick,
}: ISentMenu) => {
  const items: MenuProps["items"] = [
    {
      key: "magic-dict-current",
      label: "神奇字典",
    },
    {
      key: "show-commentary",
      label: <RelatedPara book={book} para={para} />,
    },
    {
      key: "show-nissaya",
      label: "Nissaya",
    },
    {
      key: "copy-id",
      label: "复制句子编号",
    },
    {
      key: "copy-link",
      label: "复制句子链接",
    },
    {
      type: "divider",
    },
    {
      key: "compact",
      label: (
        <Space>
          {"紧凑"}
          <Badge count="Beta" showZero color="#faad14" />
        </Space>
      ),
    },
    {
      key: "normal",
      label: "正常",
    },
  ];
  const onClick: MenuProps["onClick"] = ({ key }) => {
    console.log(`Click on item ${key}`);
    if (typeof onMenuClick !== "undefined") {
      onMenuClick(key);
    }
    switch (key) {
      case "magic-dict-current":
        if (typeof onMagicDict !== "undefined") {
          onMagicDict("current");
        }
        break;
      default:
        break;
    }
  };
  return (
    <Dropdown menu={{ items, onClick }} placement="topRight">
      <Button
        loading={loading}
        onClick={(e) => e.preventDefault()}
        icon={<MoreOutlined />}
        size="small"
        type="primary"
      />
    </Dropdown>
  );
};

export default SentMenuWidget;
