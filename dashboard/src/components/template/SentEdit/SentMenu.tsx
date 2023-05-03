import { Button, Dropdown } from "antd";
import { MoreOutlined } from "@ant-design/icons";
import type { MenuProps } from "antd";
import RelatedPara from "../../corpus/RelatedPara";

const onClick: MenuProps["onClick"] = ({ key }) => {
  console.log(`Click on item ${key}`);
  switch (key) {
    case "show-commentary":
      break;

    default:
      break;
  }
};

interface ISentMenu {
  book?: number;
  para?: number;
}
const Widget = ({ book, para }: ISentMenu) => {
  const items: MenuProps["items"] = [
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

  return (
    <Dropdown menu={{ items, onClick }} placement="topRight">
      <Button
        onClick={(e) => e.preventDefault()}
        icon={<MoreOutlined />}
        size="small"
        type="primary"
      />
    </Dropdown>
  );
};

export default Widget;
