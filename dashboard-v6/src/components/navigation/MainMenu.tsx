import { Menu, type MenuProps } from "antd";
import {
  SearchOutlined,
  HomeOutlined,
  FieldTimeOutlined,
  FolderOutlined,
} from "@ant-design/icons";

import { useLocation, useNavigate } from "react-router";
import {
  ChannelIcon,
  CourseOutLinedIcon,
  DocumentIcon,
  RobotIcon,
  TaskIcon,
  TipitakaIcon,
} from "../../assets/icon";

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
      icon: <RobotIcon />,
      label: "AI",
    },
    {
      key: "/workspace/tipitaka",
      icon: <TipitakaIcon />,
      label: "巴利三藏",
    },
    {
      key: "divider",
      type: "divider",
    },
    {
      key: "/workspace/recent",
      icon: <FieldTimeOutlined />,
      label: "最近打开",
      children: [],
    },
    {
      key: "/workspace/articles",
      icon: <DocumentIcon />,
      label: "文章",
      children: [
        {
          key: "/workspace/articles/uncategorized",
          label: "未分类",
          icon: <FolderOutlined />,
        },
        {
          key: "/workspace/articles/angl",
          label: "文集1",
          icon: <FolderOutlined />,
        },
        {
          key: "/workspace/articles",
          label: "ALL",
        },
      ],
    },
    {
      key: "/workspace/channel",
      icon: <ChannelIcon />,
      label: "频道",
    },
    {
      key: "/workspace/course",
      icon: <CourseOutLinedIcon />,
      label: "course",
    },
    {
      key: "/workspace/task",
      icon: <TaskIcon />,
      label: "task",
      children: [
        {
          key: "/workspace/task/pending",
          label: "pending",
        },
        {
          key: "/workspace/task/to-do-list",
          label: "To-do List",
        },
        {
          key: "/workspace/task/hell",
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
