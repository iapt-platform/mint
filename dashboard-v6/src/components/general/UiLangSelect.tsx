import { Button, Dropdown } from "antd";
import type { MenuProps } from "antd";
import { useMemo, useState } from "react";
import { set, get } from "../../locales";
import { GlobalOutlined } from "@ant-design/icons";

interface IUiLang {
  key: string;
  label: string;
}

const uiLang: IUiLang[] = [
  { key: "en-US", label: "English" },
  { key: "zh-Hans", label: "简体中文" },
  { key: "zh-Hant", label: "繁体中文" },
];

const UiLangSelectWidget = () => {
  /**
   * ✅ 初始值直接计算
   */
  const [curr, setCurr] = useState(() => {
    const currLang = get();
    return uiLang.find((i) => i.key === currLang)?.label;
  });

  /**
   * ✅ antd menu items 必须 useMemo + 正确类型
   */
  const items: MenuProps["items"] = useMemo(
    () =>
      uiLang.map((lang) => ({
        key: lang.key,
        label: lang.label,
      })),
    []
  );

  /**
   * ✅ 点击逻辑
   */
  const onClick: MenuProps["onClick"] = ({ key }) => {
    set(key as string); // ← 只传一个参数
    const label = uiLang.find((i) => i.key === key)?.label;
    setCurr(label);
  };

  return (
    <Dropdown menu={{ items, onClick }} placement="bottomRight">
      <Button ghost style={{ border: "unset" }} icon={<GlobalOutlined />}>
        {curr}
      </Button>
    </Dropdown>
  );
};

export default UiLangSelectWidget;
