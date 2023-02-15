import { Button, Dropdown, MenuProps } from "antd";
import { MenuOutlined } from "@ant-design/icons";

const Widget = () => {
  const items: MenuProps["items"] = [
    {
      key: "new",
      label: "最新",
    },
    {
      key: "palicanon",
      label: "圣典",
    },
  ];
  const onClick: MenuProps["onClick"] = (e) => {
    console.log("click ", e);
    switch (e.key) {
      case "showCol":
        break;
      default:
        break;
    }
  };
  return (
    <Dropdown
      menu={{ items, onClick }}
      placement="bottomLeft"
      trigger={["click"]}
    >
      <Button
        style={{ display: "block" }}
        size="small"
        icon={<MenuOutlined />}
      ></Button>
    </Dropdown>
  );
};

export default Widget;
