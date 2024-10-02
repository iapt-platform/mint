import { Link } from "react-router-dom";
import { Card } from "antd";
import {
  AppstoreOutlined,
  LikeOutlined,
  FieldTimeOutlined,
} from "@ant-design/icons";
import { Space } from "antd";

interface IIconParamListData {
  label: React.ReactNode;
  key: string;
  icon: React.ReactNode;
}
interface IWidgetIconParamList {
  data: IIconParamListData[];
}
const IconParamList = (prop: IWidgetIconParamList) => {
  return (
    <>
      <Space>
        {prop.data.map((item, id) => {
          return (
            <Space key={id}>
              {item.icon} {item.label}
            </Space>
          );
        })}
      </Space>
    </>
  );
};

export interface ITopArticleCardData {
  title: string;
  link: string;
  like: number;
  hit: number;
  updatedAt: string;
}
interface IWidgetTopArticleCard {
  data: ITopArticleCardData;
}
const TopArticleCardWidget = (prop: IWidgetTopArticleCard) => {
  const items: IIconParamListData[] = [
    {
      label: "经藏",
      key: "sutta",
      icon: <AppstoreOutlined />,
    },
    {
      label: prop.data.like,
      key: "like",
      icon: <LikeOutlined />,
    },
    {
      label: prop.data.updatedAt,
      key: "updated",
      icon: <FieldTimeOutlined />,
    },
  ];

  return (
    <>
      <Card>
        <h4>
          <Link to={prop.data.link}>{prop.data.title}</Link>
        </h4>
        <div>
          <IconParamList data={items} />
        </div>
      </Card>
    </>
  );
};

export default TopArticleCardWidget;
