//课程主页
import { Link } from "react-router-dom";
import { Space, Input } from "antd";
import { Layout, Affix, Col, Row, Divider } from "antd";

import LecturerList from "../../../components/library/course/LecturerList";
import CourseList from "../../../components/library/course/CourseList";
const { Content, Header } = Layout;
const { Search } = Input;
const Widget = () => {
  // TODO i18n
  return (
    <Layout>
      <Header style={{ height: 200 }}>
        <h1 style={{"color": "white", "fontWeight": 'bold', "fontSize": 40}}>课程</h1>
        <p style={{"color": "white", "fontSize": 17}}>看看世界各地的巴利专家都是如何解析圣典的</p>
      </Header>


      <Content>
        <Row>
          <Col flex="auto"></Col>
          <Col flex="1260px">
          <Row>
              
          <h1>主讲人</h1>
              </Row>
            <Row>
              
            <div>
              <LecturerList />
            </div>
            </Row>
          </Col>
          <Col flex="auto"></Col>
        </Row>
        <Space></Space>
        <Row>
          <Col flex="auto"></Col>
          <Col flex="1260px">
          <Row>
          <Divider />   
          <h1>正在进行</h1>
              </Row>
            <Row>
              
            <div>
              <CourseList />
            </div>
            </Row>
          </Col>
          <Col flex="auto"></Col>
        </Row>
        <Space></Space>
        <Row>
          <Col flex="auto"></Col>
          <Col flex="1260px">
          <Row>
          <Divider />
          <h1>已经结束</h1>
              </Row>
            <Row>
              
            <div>
              <CourseList />
            </div>
            </Row>
          </Col>
          <Col flex="auto"></Col>
        </Row>
      </Content>
    </Layout>

  );
};

export default Widget;
