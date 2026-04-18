// src/components/ThemeSwitch.tsx
import { Dropdown, type MenuProps } from "antd";
import {
  DesktopOutlined,
  SunOutlined,
  MoonOutlined,
  CheckOutlined,
} from "@ant-design/icons";
import { useAppSelector } from "../../hooks";
import {
  mode as _mode,
  themeChange,
  type TThemeMode,
} from "../../reducers/theme";
import store from "../../store";

const icons = {
  system: <DesktopOutlined />,
  light: <SunOutlined />,
  dark: <MoonOutlined />,
};

const labels = {
  system: "跟随系统",
  light: "亮色主题",
  dark: "暗色主题",
};

const ThemeSwitch = () => {
  const themeMode = useAppSelector(_mode);

  const items: MenuProps["items"] = (
    ["system", "light", "dark"] as TThemeMode[]
  ).map((key) => ({
    key,
    icon: icons[key],
    label: labels[key],
    itemIcon: themeMode === key ? <CheckOutlined /> : null,
  }));

  return (
    <Dropdown
      menu={{
        items,
        onClick: ({ key }) => store.dispatch(themeChange(key as TThemeMode)),
      }}
      trigger={["click"]}
    >
      <span style={{ cursor: "pointer", fontSize: 18 }}>
        {icons[themeMode]}
      </span>
    </Dropdown>
  );
};

export default ThemeSwitch;
