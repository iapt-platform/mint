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
        style={{ display: "block", color: "white" }}
        icon={<AppstoreOutlined />}
      />
    </Dropdown>
  );
};

export default MainMenuWidget;
