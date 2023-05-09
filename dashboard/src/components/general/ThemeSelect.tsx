import { Switch } from "antd";
import { useEffect, useState } from "react";
import { refresh } from "../../reducers/theme";
import store from "../../store";

const ThemeSelectWidget = () => {
  const [isDark, setIsDark] = useState<boolean>();
  useEffect(() => {
    const currTheme = localStorage.getItem("theme");
    if (currTheme === "dark") {
      setIsDark(true);
    } else {
      setIsDark(false);
    }
  }, []);

  return (
    <Switch
      defaultChecked={isDark}
      checkedChildren={"🌞"}
      unCheckedChildren={"🌙"}
      onChange={(checked: boolean) => {
        console.log(`switch to ${checked}`);
        if (checked) {
          store.dispatch(refresh("dark"));
          localStorage.setItem("theme", "dark");
        } else {
          store.dispatch(refresh("ant"));
          localStorage.setItem("theme", "ant");
        }
      }}
    />
  );
};

export default ThemeSelectWidget;
