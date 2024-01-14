import { Dropdown, Tooltip, notification } from "antd";
import {
  CopyOutlined,
  ShoppingCartOutlined,
  CheckOutlined,
  DownOutlined,
} from "@ant-design/icons";
import { useEffect, useState } from "react";
import { IWbw } from "../Wbw/WbwWord";
import store from "../../../store";
import { modeChange } from "../../../reducers/cart-mode";
import { useAppSelector } from "../../../hooks";
import { mode as _mode } from "../../../reducers/cart-mode";
import { addToCart } from "./SentCart";

interface IWidget {
  text?: string;
  wbwData?: IWbw[];
}
const SentTabCopyWidget = ({ text, wbwData }: IWidget) => {
  const [mode, setMode] = useState("copy");
  const [success, setSuccess] = useState(false);
  const currMode = useAppSelector(_mode);

  useEffect(() => {
    const modeSetting = localStorage.getItem("cart/mode");
    if (modeSetting === "cart") {
      setMode("cart");
    }
  }, []);

  useEffect(() => {
    localStorage.setItem("cart/mode", mode);
  }, [mode]);

  useEffect(() => {
    if (currMode) {
      setMode(currMode);
    }
  }, [currMode]);

  const copy = (mode: string) => {
    if (text) {
      if (mode === "copy") {
        navigator.clipboard.writeText(text).then(() => {
          setSuccess(true);
          setTimeout(() => setSuccess(false), 3000);
        });
      } else {
        const paliText = wbwData
          ?.filter((value) => value.type?.value !== ".ctl.")
          .map((item) => item.word.value)
          .join(" ");

        addToCart([{ id: text, text: paliText ? paliText : "" }]);
        notification.success({
          message: "句子已经添加到Cart",
        });
        setSuccess(true);
        setTimeout(() => setSuccess(false), 3000);
      }
    }
  };
  return (
    <Dropdown.Button
      size="small"
      type="link"
      icon={<DownOutlined />}
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
          store.dispatch(modeChange(e.key));
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
