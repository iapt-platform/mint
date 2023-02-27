import { Button, Dropdown } from "antd";
import { MenuOutlined } from "@ant-design/icons";
import { mainMenuItems } from "../library/HeadBar";

const Widget = () => {
  return (
    <Dropdown
      menu={{ items: mainMenuItems }}
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
