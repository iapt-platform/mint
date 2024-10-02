import { Button, Dropdown } from "antd";
import type { MenuProps } from "antd";
import { useEffect, useState } from "react";
import { set, get } from "../../locales";
import { GlobalOutlined } from "@ant-design/icons";

interface IUiLang {
  key: string;
  label: string;
}
const uiLang: IUiLang[] = [
  {
    key: "en-US",
    label: "English",
  },
  {
    key: "zh-Hans",
    label: "简体中文",
  },
  {
    key: "zh-Hant",
    label: "繁体中文",
  },
];

const UiLangSelectWidget = () => {
  const [curr, setCurr] = useState<string>();

  const items: MenuProps["items"] = uiLang;

  const onClick: MenuProps["onClick"] = ({ key }) => {
    set(key, true);
  };

  useEffect(() => {
    const currLang = get();
    console.log("lang", currLang);
    const find = uiLang.find((item) => item?.key === currLang);
    setCurr(find?.label);
  }, []);

  return (
    <Dropdown menu={{ items, onClick }} placement="bottomRight">
      <Button ghost style={{ border: "unset" }} icon={<GlobalOutlined />}>
        {curr}
      </Button>
    </Dropdown>
  );
};

export default UiLangSelectWidget;
