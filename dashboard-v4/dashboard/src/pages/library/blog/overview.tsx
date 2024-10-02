import { useParams } from "react-router-dom";
import { Row, Col, Space } from "antd";
import { Affix } from "antd";

import BlogNav from "../../../components/blog/BlogNav";
import Profile from "../../../components/blog/Profile";
import AuthorTimeLine from "../../../components/blog/TimeLine";
import TopChapter from "../../../components/corpus/TopChapter";

const Widget = () => {
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
          <Space direction="vertical" size={50}>
            <TopChapter studioName={studio} />
            <AuthorTimeLine studioName={studio} />
          </Space>
        </Col>
      </Row>
    </>
  );
};

export default Widget;
