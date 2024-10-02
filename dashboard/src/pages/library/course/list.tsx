//课程主页
import { Layout, Col, Row, Divider, Typography } from "antd";

import LecturerList from "../../../components/course/LecturerList";
import CourseList from "../../../components/course/CourseList";
const { Content, Header } = Layout;
const { Title } = Typography;

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
            <Title level={4}>最新</Title>
            <LecturerList />
            <Divider />
            <Title level={4}>开放报名</Title>
            <CourseList type="open" />
            <Divider />
            <Title level={4}>历史课程</Title>
            <CourseList type="close" />
          </Col>
          <Col flex="auto"></Col>
        </Row>
      </Content>
    </Layout>
  );
};

export default Widget;
