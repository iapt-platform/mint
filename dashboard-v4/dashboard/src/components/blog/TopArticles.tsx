import { Row, Col } from "antd";

import TopArticleCard, { ITopArticleCardData } from "./TopArticleCard";

interface IWidgetTopArticles {
  studio: string;
}
const TopArticlesWidget = (prop: IWidgetTopArticles) => {
  const data: ITopArticleCardData[] = [
    {
      title: "法句心品",
      link: "/my/article/12345",
      like: 50,
      hit: 100,
      updatedAt: "2022-12-3",
    },
    {
      title: "法句心品",
      link: "/my/article/12345",
      like: 50,
      hit: 100,
      updatedAt: "2022-12-3",
    },
    {
      title: "疑惑度脱-应学法",
      link: "/my/article/12345",
      like: 50,
      hit: 100,
      updatedAt: "2022-12-3",
    },
    {
      title: "法句心品",
      link: "/my/article/12345",
      like: 50,
      hit: 100,
      updatedAt: "2022-12-3",
    },
  ];

  const list = data.map((item, id) => {
    return (
      <Col flex="400px" key={id}>
        <TopArticleCard data={item} />
      </Col>
    );
  });
  return <Row>{list}</Row>;
};

export default TopArticlesWidget;
