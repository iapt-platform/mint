import { Button, Dropdown } from "antd";
import { MoreOutlined } from "@ant-design/icons";
import type { MenuProps } from "antd";
import RelatedPara from "../../corpus/RelatedPara";
import { useState } from "react";

interface ISentMenu {
  book?: number;
  para?: number;
  onMagicDict?: Function;
}
const SentMenuWidget = ({ book, para, onMagicDict }: ISentMenu) => {
  const [loading, setLoading] = useState(false);
  const items: MenuProps["items"] = [
    {
      key: "magic-dict-current",
      label: "魔法字典",
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
  ];
  const onClick: MenuProps["onClick"] = ({ key }) => {
    console.log(`Click on item ${key}`);
    switch (key) {
      case "magic-dict-current":
        if (typeof onMagicDict !== "undefined") {
          setLoading(true);
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
