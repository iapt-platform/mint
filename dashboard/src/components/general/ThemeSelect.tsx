import { Button, Dropdown, MenuProps } from "antd";
import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import { ThemeOutlinedIcon } from "../../assets/icon";
import { refresh } from "../../reducers/theme";
import store from "../../store";

const ThemeSelectWidget = () => {
  const intl = useIntl();
  const [theme, setTheme] = useState<string>("ant");

  const items: MenuProps["items"] = [
    {
      key: "ant",
      label: "默认",
    },
    {
      key: "dark",
      label: "夜间",
    },
  ];

  const onClick: MenuProps["onClick"] = ({ key }) => {
    store.dispatch(refresh(key));
    localStorage.setItem("theme", key);
  };

  useEffect(() => {
    const currTheme = localStorage.getItem("theme");
    if (currTheme) {
      setTheme(currTheme);
    } else {
      setTheme("ant");
    }
  }, []);

  return (
    <Dropdown menu={{ items, onClick }} placement="bottomRight">
      <Button
        ghost
        style={{ border: "unset" }}
        icon={<ThemeOutlinedIcon style={{ color: "white" }} />}
      >
        {intl.formatMessage({
          id: `buttons.theme.${theme}`,
        })}
      </Button>
    </Dropdown>
  );
};

export default ThemeSelectWidget;
