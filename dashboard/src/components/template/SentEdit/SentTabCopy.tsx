import { Dropdown, Tooltip } from "antd";
import {
  CopyOutlined,
  ShoppingCartOutlined,
  CheckOutlined,
} from "@ant-design/icons";
import { useState } from "react";

interface IWidget {
  text?: string;
}
const SentTabCopyWidget = ({ text }: IWidget) => {
  const [mode, setMode] = useState("copy");
  const [success, setSuccess] = useState(false);
  const copy = (mode: string) => {
    if (text) {
      if (mode === "copy") {
        navigator.clipboard.writeText(text).then(() => {
          setSuccess(true);
          setTimeout(() => setSuccess(false), 3000);
        });
      } else {
        const oldText = localStorage.getItem("cart/text");
        let cartText: string[] = [];
        if (oldText) {
          cartText = JSON.parse(oldText);
        }
        cartText.push(text);
        localStorage.setItem("cart/text", JSON.stringify(cartText));
        setSuccess(true);
        setTimeout(() => setSuccess(false), 3000);
      }
    }
  };
  return (
    <Dropdown.Button
      size="small"
      type="link"
      menu={{
        items: [
          {
            label: "copy",
            key: "copy",
            icon: <CopyOutlined />,
          },
          {
            label: "add to cart",
            key: "cart",
            icon: <ShoppingCartOutlined />,
          },
        ],
        onClick: (e) => {
          setMode(e.key);
          copy(e.key);
        },
      }}
      onClick={() => copy(mode)}
    >
      <Tooltip title={(success ? "已经" : "") + `${mode}`}>
        {success ? (
          <CheckOutlined />
        ) : mode === "copy" ? (
          <CopyOutlined />
        ) : (
          <ShoppingCartOutlined />
        )}
      </Tooltip>
    </Dropdown.Button>
  );
};

export default SentTabCopyWidget;
