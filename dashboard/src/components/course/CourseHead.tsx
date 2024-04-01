//课程详情图片标题按钮主讲人组合
import { Link } from "react-router-dom";
import { Image, Space, Col, Row, Breadcrumb } from "antd";
import { Typography } from "antd";
import { HomeOutlined } from "@ant-design/icons";

import { IUser } from "../auth/User";
import { API_HOST } from "../../request";
import UserName from "../auth/UserName";
import { TCourseJoinMode } from "../api/Course";
import { useIntl } from "react-intl";
import Status from "./Status";
import moment from "moment";

const { Title, Text } = Typography;

const courseDuration = (startAt?: string, endAt?: string) => {
  let labelDuration = "";
  if (moment().isBefore(startAt)) {
    labelDuration = "未开始";
  } else if (moment().isBefore(endAt)) {
    labelDuration = "进行中";
  } else {
    labelDuration = "已经结束";
  }
  return labelDuration;
};

interface IWidget {
  id?: string;
  title?: string;
  subtitle?: string;
  coverUrl?: string[];
  startAt?: string;
  endAt?: string;
  teacher?: IUser;
  join?: TCourseJoinMode;
}
const CourseHeadWidget = ({
  id,
  title,
  subtitle,
  coverUrl,
  teacher,
  startAt,
  endAt,
  join,
}: IWidget) => {
  const intl = useIntl();
  const duration = courseDuration(startAt, endAt);
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
                src={coverUrl && coverUrl.length > 1 ? coverUrl[1] : undefined}
                preview={{
                  src:
                    coverUrl && coverUrl.length > 0 ? coverUrl[0] : undefined,
                }}
                fallback={`${API_HOST}/app/course/img/default.jpg`}
              />
              <Space direction="vertical">
                <Title level={3}>{title}</Title>
                <Title level={5}>{subtitle}</Title>

                <Text>
                  {moment(startAt).format("YYYY-MM-DD")}——
                  {moment(endAt).format("YYYY-MM-DD")}
                </Text>
                <Text>{duration}</Text>
                <Text>
                  {join
                    ? intl.formatMessage({
                        id: `course.join.mode.${join}.message`,
                      })
                    : undefined}
                </Text>
                {id ? (
                  <Status
                    courseId={id}
                    courseName={title}
                    joinMode={join}
                    startAt={startAt}
                    endAt={endAt}
                  />
                ) : undefined}
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

export default CourseHeadWidget;
