//课程详情图片标题按钮主讲人组合
import { Link } from "react-router-dom";
import { Image, Space, Col, Row, Breadcrumb } from "antd";
import { Typography } from "antd";
import { HomeOutlined } from "@ant-design/icons";

import { IUser } from "../auth/User";
import { API_HOST } from "../../request";
import UserName from "../auth/UserName";
import { TCourseExpRequest, TCourseJoinMode } from "../api/Course";
import { useIntl } from "react-intl";
import Status from "./Status";

const { Title, Text } = Typography;

interface IWidget {
  id?: string;
  title?: string;
  subtitle?: string;
  coverUrl?: string;
  startAt?: string;
  endAt?: string;
  teacher?: IUser;
  join?: TCourseJoinMode;
  exp?: TCourseExpRequest;
}
const Widget = ({
  id,
  title,
  subtitle,
  coverUrl,
  teacher,
  startAt,
  endAt,
  join,
  exp,
}: IWidget) => {
  const intl = useIntl();

  return (
    <>
      <Row>
        <Col flex="auto"></Col>
        <Col flex="960px">
          <Space direction="vertical">
            <Breadcrumb>
              <Breadcrumb.Item>
                <HomeOutlined />
              </Breadcrumb.Item>
              <Breadcrumb.Item>
                <Link to="/course/list">
                  <Text>课程</Text>
                </Link>
              </Breadcrumb.Item>
              <Breadcrumb.Item>{title}</Breadcrumb.Item>
            </Breadcrumb>
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

                <Text>
                  {startAt}——{endAt}
                </Text>
                <Text>
                  {join
                    ? intl.formatMessage({
                        id: `course.join.mode.${join}.message`,
                      })
                    : undefined}
                </Text>
                <Status
                  courseId={id ? id : ""}
                  expRequest={exp}
                  joinMode={join}
                  startAt={startAt}
                />
              </Space>
            </Space>

            <Space>
              <Text>主讲人：</Text>{" "}
              <Text>
                <UserName {...teacher} />
              </Text>
            </Space>
          </Space>
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
