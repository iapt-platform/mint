import { App, Dropdown, Tooltip } from "antd";
import {
  CopyOutlined,
  ShoppingCartOutlined,
  CheckOutlined,
  DownOutlined,
} from "@ant-design/icons";
import { useEffect, useState } from "react";

import store from "../../store";
import { modeChange, type TCopyMode } from "../../reducers/cart-mode";
import { useAppSelector } from "../../hooks";
import { mode as _mode } from "../../reducers/cart-mode";
import type { IWbw } from "../../types/wbw";
import { addToCart } from "./utils";

interface IWidget {
  text?: string;
  wbwData?: IWbw[];
}

const SentTabCopyWidget = ({ text, wbwData }: IWidget) => {
  const { notification } = App.useApp();
  // ✅ 直接读取 Redux，不再维护镜像 local state
  const mode = useAppSelector(_mode);
  const [success, setSuccess] = useState(false);

  // ✅ Redux 变化时同步写入 localStorage（外部系统同步，effect 正当用途）
  useEffect(() => {
    if (mode) {
      localStorage.setItem("cart/mode", mode);
    }
  }, [mode]);

  const handleCopy = (targetMode: TCopyMode) => {
    if (!text) return;

    if (targetMode === "single") {
      navigator.clipboard.writeText(text).then(() => {
        setSuccess(true);
        setTimeout(() => setSuccess(false), 3000);
      });
    } else {
      const paliText =
        wbwData
          ?.filter((item) => item.type?.value !== ".ctl.")
          .map((item) => item.word.value)
          .join(" ") ?? "";

      addToCart([{ id: text, text: paliText }]);
      notification.success({ message: "句子已经添加到Cart" });
      setSuccess(true);
      setTimeout(() => setSuccess(false), 3000);
    }
  };

  const handleMenuClick = (key: string) => {
    const newMode = key as TCopyMode;
    store.dispatch(modeChange(newMode));
    handleCopy(newMode);
  };

  const icon = success ? (
    <CheckOutlined />
  ) : mode === "single" ? (
    <CopyOutlined />
  ) : (
    <ShoppingCartOutlined />
  );

  return (
    <Dropdown.Button
      size="small"
      type="link"
      icon={<DownOutlined />}
      menu={{
        items: [
          { label: "copy", key: "copy", icon: <CopyOutlined /> },
          { label: "add to cart", key: "cart", icon: <ShoppingCartOutlined /> },
        ],
        onClick: (e) => handleMenuClick(e.key),
      }}
      onClick={() => handleCopy(mode)}
    >
      <Tooltip title={`${success ? "已完成：" : ""}${mode}`}>{icon}</Tooltip>
    </Dropdown.Button>
  );
};

export default SentTabCopyWidget;
