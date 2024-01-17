import { useIntl } from "react-intl";
import { Badge, Dropdown } from "antd";
import type { MenuProps } from "antd";
import { BlockOutlined, CalendarOutlined } from "@ant-design/icons";

const handleButtonClick = (e: React.MouseEvent<HTMLButtonElement>) => {
  console.log("click left button", e);
};

interface IWidget {
  style?: React.CSSProperties;
  sentId: string;
  count?: number;
  onMenuClick?: Function;
}
const SentTabButtonWidget = ({
  style,
  sentId,
  onMenuClick,
  count = 0,
}: IWidget) => {
  const intl = useIntl();
  const items: MenuProps["items"] = [
    {
      label: "排序",
      key: "orderby",
      icon: <CalendarOutlined />,
      children: [
        {
          label: "完成度",
          key: "progress",
        },
        {
          label: "问题数量",
          key: "qa",
        },
      ],
    },
    {
      label: "显示完成度",
      key: "show-progress",
      icon: <BlockOutlined />,
    },
  ];
  const handleMenuClick: MenuProps["onClick"] = (e) => {
    e.domEvent.stopPropagation();
    if (typeof onMenuClick !== "undefined") {
      onMenuClick(e.keyPath);
    }
  };
  const menuProps = {
    items,
    onClick: handleMenuClick,
  };

  return (
    <Dropdown.Button
      style={style}
      size="small"
      type="text"
      menu={menuProps}
      onClick={handleButtonClick}
    >
      {intl.formatMessage({
        id: "buttons.wbw",
      })}
      <Badge size="small" color="geekblue" count={count}></Badge>
    </Dropdown.Button>
  );
};

export default SentTabButtonWidget;
