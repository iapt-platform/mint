import { useState } from "react";
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
  children?: React.ReactNode;
}
const Widget = ({ book, para, children }: ISentMenu) => {
  const [isHover, setIsHover] = useState(false);
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
    <div
      onMouseEnter={() => {
        setIsHover(true);
      }}
      onMouseLeave={() => {
        setIsHover(false);
      }}
    >
      <div
        style={{
          marginTop: "-1.5em",
          position: "absolute",
          display: isHover ? "block" : "none",
        }}
      >
        <Dropdown menu={{ items, onClick }} placement="bottomLeft">
          <Button
            onClick={(e) => e.preventDefault()}
            type="primary"
            icon={<MoreOutlined />}
            size="small"
          />
        </Dropdown>
      </div>
      {children}
    </div>
  );
};

export default Widget;
