//课程详情图片标题按钮主讲人组合
import { Link } from "react-router-dom";
import { Image, Button, Space, Col, Row } from "antd";
import { Typography } from "antd";

import { IUser } from "../../auth/User";
import { API_HOST } from "../../../request";
import UserName from "../../auth/UserName";

const { Title } = Typography;

interface IWidget {
  title?: string;
  subtitle?: string;
  coverUrl?: string;
  teacher?: IUser;
}
const Widget = ({ title, subtitle, coverUrl, teacher }: IWidget) => {
  return (
    <>
      <Row>
        <Col flex="auto"></Col>
        <Col flex="1260px">
          <Space>
            <Image
              width={200}
              style={{ borderRadius: 12 }}
              src={API_HOST + "/" + coverUrl}
              fallback={`${API_HOST}/app/course/img/default.jpg`}
            />
            <Space direction="vertical">
              <Title level={3}>{title}</Title>
              <Title level={5}>{subtitle}</Title>
            </Space>
          </Space>
          <Button type="primary">关注</Button>
          <div>
            主讲人： <UserName {...teacher} />
          </div>
        </Col>
        <Col flex="auto"></Col>
      </Row>
    </>
  );
};

export default Widget;

/*
<Button type="primary">关注</Button>
<Button type="primary" disabled>
  已关注
</Button>
*/
