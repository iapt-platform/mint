import { Menu, type MenuProps } from "antd";
import {
  SearchOutlined,
  HomeOutlined,
  RobotOutlined,
  BookOutlined,
} from "@ant-design/icons";

import { useLocation, useNavigate } from "react-router";

interface Props {
  onSearch?: () => void;
}
const Widget = ({ onSearch }: Props) => {
  const location = useLocation();
  const navigate = useNavigate();

  const items: MenuProps["items"] = [
    {
      key: "search",
      icon: <SearchOutlined />,
      label: "搜索",
    },
    {
      key: "/workspace/home",
      icon: <HomeOutlined />,
      label: "主页",
    },
    {
      key: "/workspace/ai",
      icon: <RobotOutlined />,
      label: "AI",
    },
    {
      key: "/workspace/tipitaka",
      icon: <BookOutlined />,
      label: "巴利三藏",
    },
    {
      key: "divider",
      type: "divider",
    },
    {
      key: "/workspace/recent",
      icon: <BookOutlined />,
      label: "最近打开",
      children: [],
    },
    {
      key: "/workspace/anthology",
      icon: <BookOutlined />,
      label: "文集",
    },
    {
      key: "/workspace/channel",
      icon: <BookOutlined />,
      label: "频道",
    },
    {
      key: "/workspace/task",
      icon: <BookOutlined />,
      label: "task",
      children: [
        {
          key: "/workspace/task/hell",
          icon: <BookOutlined />,
          label: "task hell",
        },
      ],
    },
  ];

  /** 当前高亮规则 */
  const selectedKey: string =
    location.pathname === "/"
      ? "/"
      : (items?.find(
          (i) =>
            i &&
            "key" in i &&
            typeof i.key === "string" &&
            location.pathname.startsWith(i.key)
        )?.key as string) || "";

  /** 点击菜单 */
  const handleClick = ({ key }: { key: string }) => {
    if (key === "search") {
      onSearch?.();
      return;
    }
    navigate(key);
  };

  return (
    <Menu
      mode="inline"
      selectedKeys={[selectedKey]}
      items={items}
      onClick={handleClick}
      style={{ borderRight: 0 }}
    />
  );
};

export default Widget;
