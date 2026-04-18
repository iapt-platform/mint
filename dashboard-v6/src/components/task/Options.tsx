import { Button, Dropdown, type MenuProps } from "antd";
import { useState } from "react";

export interface IMenu {
  key: string;
  label: string;
}

interface IWidget {
  items: IMenu[];
  icon?: React.ReactNode;
  text?: string;
  initKey?: string;
  onChange?: (key: string) => void;
}

const Options = ({ items, icon, text, initKey = "1", onChange }: IWidget) => {
  const [currKey, setCurrKey] = useState(initKey);
  const currValue = items.find(
    (item) => item.key === (currKey ?? initKey)
  )?.label;

  const onClick: MenuProps["onClick"] = ({ key }) => {
    onChange?.(key);
    setCurrKey(key);
  };

  const menuItems: MenuProps["items"] = items.map(({ key, label }) => ({
    key,
    label,
  }));

  return (
    <Dropdown
      menu={{
        items: menuItems,
        onClick,
        selectable: true,
        defaultSelectedKeys: [currKey],
      }}
      trigger={["click"]}
      placement="bottomLeft"
    >
      <Button type="text" icon={icon}>
        {text}
        {currValue}
      </Button>
    </Dropdown>
  );
};

export default Options;
