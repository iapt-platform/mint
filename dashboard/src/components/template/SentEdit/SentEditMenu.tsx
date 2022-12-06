import { Button, Dropdown, Menu } from "antd";
import { useState } from "react";
import { EditOutlined, CopyOutlined, MoreOutlined } from "@ant-design/icons";

interface ISentEditMenu {
  children?: React.ReactNode;
  onModeChange?: Function;
}
const Widget = ({ children, onModeChange }: ISentEditMenu) => {
  const [isHover, setIsHover] = useState(false);

  const menu = (
    <Menu
      onClick={(e) => {
        console.log(e);
      }}
      items={[
        {
          key: "en",
          label: "时间线",
        },
        {
          key: "zh-Hans",
          label: "分享",
        },
      ]}
    />
  );

  return (
    <div
      onMouseEnter={() => {
        setIsHover(true);
      }}
      onMouseLeave={() => {
        setIsHover(false);
      }}
    >
      <div
        style={{
          marginTop: "-1.2em",
          right: "0",
          position: "absolute",
          display: isHover ? "block" : "none",
        }}
      >
        <Button
          icon={<EditOutlined />}
          size="small"
          onClick={() => {
            if (typeof onModeChange !== "undefined") {
              onModeChange("edit");
            }
          }}
        />
        <Button icon={<CopyOutlined />} size="small" />
        <Dropdown overlay={menu} placement="bottomRight">
          <Button icon={<MoreOutlined />} size="small" />
        </Dropdown>
      </div>
      {children}
    </div>
  );
};

export default Widget;
