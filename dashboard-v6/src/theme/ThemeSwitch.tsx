import React from "react";
import { Switch } from "antd";
import { MoonOutlined, SunOutlined } from "@ant-design/icons";

interface ThemeSwitchProps {
  theme: "light" | "dark";
  onChange: (theme: "light" | "dark") => void;
}

const ThemeSwitch: React.FC<ThemeSwitchProps> = ({ theme, onChange }) => {
  return (
    <Switch
      checked={theme === "dark"}
      onChange={(checked) => onChange(checked ? "dark" : "light")}
      checkedChildren={<MoonOutlined />}
      unCheckedChildren={<SunOutlined />}
    />
  );
};

export default ThemeSwitch;
