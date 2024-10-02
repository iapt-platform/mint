//课程详情图片标题按钮主讲人组合
import { Link } from "react-router-dom";
import { Image, Space, Col, Row, Breadcrumb, Tag } from "antd";
import { Typography } from "antd";
import { HomeOutlined } from "@ant-design/icons";

import { API_HOST } from "../../request";
import { ICourseDataResponse } from "../api/Course";
import { useIntl } from "react-intl";
import Status from "./Status";
import moment from "moment";
import User from "../auth/User";

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
  return <Tag>{labelDuration}</Tag>;
};

interface IWidget {
  data?: ICourseDataResponse;
}
const CourseHeadWidget = ({ data }: IWidget) => {
  const intl = useIntl();
  const duration = courseDuration(data?.start_at, data?.end_at);
  let signUp = "";
  if (moment().isBefore(moment(data?.sign_up_start_at))) {
    signUp = "未开始";
  } else if (
    moment().isBetween(
      moment(data?.sign_up_start_at),
      moment(data?.sign_up_end_at)
    )
  ) {
    signUp = "可报名";
  } else if (moment().isAfter(moment(data?.sign_up_end_at))) {
    signUp = "已结束";
  }
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
              <Breadcrumb.Item>{data?.title}</Breadcrumb.Item>
            </Breadcrumb>
            <Space>
              <Image
                width={200}
                style={{ borderRadius: 12 }}
                src={
                  data?.cover_url && data?.cover_url.length > 1
                    ? data?.cover_url[1]
                    : undefined
                }
                preview={{
                  src:
                    data?.cover_url && data?.cover_url.length > 0
                      ? data?.cover_url[0]
                      : undefined,
                }}
                fallback={`${API_HOST}/app/course/img/default.jpg`}
              />
              <Space direction="vertical">
                <Title level={3}>{data?.title}</Title>
                <Title level={5}>{data?.subtitle}</Title>
                <Text>
                  <Space>
                    {"报名时间:"}
                    {moment(data?.sign_up_start_at).format("YYYY-MM-DD")}——
                    {moment(data?.sign_up_end_at).format("YYYY-MM-DD")}
                    <Tag>{signUp}</Tag>
                  </Space>
                </Text>
                <Text>
                  <Space>
                    {"课程时间:"}
                    {moment(data?.start_at).format("YYYY-MM-DD")}——
                    {moment(data?.end_at).format("YYYY-MM-DD")}
                    {duration}
                  </Space>
                </Text>
                <Text>
                  {data?.join
                    ? intl.formatMessage({
                        id: `course.join.mode.${data.join}.message`,
                      })
                    : undefined}
                </Text>

                <Status data={data} />
              </Space>
            </Space>

            <Space>
              <Text>主讲人：</Text>{" "}
              <Text>
                <User {...data?.teacher} showAvatar={false} />
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
