import { Button, Dropdown } from "antd";
import { AppstoreOutlined } from "@ant-design/icons";
import { mainMenuItems } from "../library/HeadBar";

const MainMenuWidget = () => {
  return (
    <Dropdown
      menu={{ items: mainMenuItems }}
      placement="bottomLeft"
      trigger={["click"]}
    >
      <Button
        type="text"
        style={{ display: "block" }}
        icon={<AppstoreOutlined />}
      ></Button>
    </Dropdown>
  );
};

export default MainMenuWidget;
