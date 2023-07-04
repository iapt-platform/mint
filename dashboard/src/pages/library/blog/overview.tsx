import { useParams } from "react-router-dom";
import { Row, Col } from "antd";
import { Affix } from "antd";

import BlogNav from "../../../components/blog/BlogNav";
import Profile from "../../../components/blog/Profile";
import AuthorTimeLine from "../../../components/blog/TimeLine";
import TopArticles from "../../../components/blog/TopArticles";

const Widget = () => {
  // TODO
  const { studio } = useParams(); //url 参数

  return (
    <>
      <Affix offsetTop={0}>
        <BlogNav selectedKey="overview" studio={studio ? studio : ""} />
      </Affix>

      <Row>
        <Col flex="300px">
          <Profile />
        </Col>

        <Col flex="900px">
          <div>
            <TopArticles studio={studio ? studio : ""} />
          </div>
          <div>
            <AuthorTimeLine studioName={studio} />
          </div>
        </Col>
      </Row>
    </>
  );
};

export default Widget;
