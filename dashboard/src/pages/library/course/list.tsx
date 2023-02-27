//课程主页
import { Layout, Col, Row, Divider } from "antd";

import LecturerList from "../../../components/course/LecturerList";
import CourseList from "../../../components/course/CourseList";
const { Content, Header } = Layout;

const Widget = () => {
  // TODO i18n
  return (
    <Layout>
      <Header style={{ height: 200 }}>
        <h1 style={{ color: "white", fontWeight: "bold", fontSize: 40 }}>
          课程
        </h1>
        <p style={{ color: "white", fontSize: 17 }}>
          看看世界各地的巴利专家都是如何解析圣典的
        </p>
      </Header>

      <Content>
        <Row>
          <Col flex="auto"></Col>
          <Col flex="960px">
            <Row>
              <h1>最新</h1>
            </Row>
            <Row>
              <LecturerList />
            </Row>
            <Divider />
            <Row>
              <h1>开放报名</h1>
            </Row>
            <Row>
              <CourseList type="open" />
            </Row>
            <Divider />
            <Row>
              <h1>历史课程</h1>
            </Row>
            <Row>
              <CourseList type="close" />
            </Row>
          </Col>
          <Col flex="auto"></Col>
        </Row>
      </Content>
    </Layout>
  );
};

export default Widget;
