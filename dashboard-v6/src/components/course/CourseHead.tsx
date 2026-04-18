//课程详情图片标题按钮主讲人组合

import { Image, Space, Col, Row, Tag } from "antd";
import { Typography } from "antd";

import type { ICourseDataResponse } from "../../api/course";
import { useIntl } from "react-intl";
import Status from "./Status";
import dayjs from "dayjs";
import isBetween from "dayjs/plugin/isBetween";
import User from "../auth/User";

const { Title, Text } = Typography;

dayjs.extend(isBetween);

const courseDuration = (startAt?: string, endAt?: string) => {
  let labelDuration = "";
  if (dayjs().isBefore(startAt)) {
    labelDuration = "未开始";
  } else if (dayjs().isBefore(endAt)) {
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
  if (dayjs().isBefore(dayjs(data?.sign_up_start_at))) {
    signUp = "未开始";
  } else if (
    dayjs().isBetween(
      dayjs(data?.sign_up_start_at),
      dayjs(data?.sign_up_end_at)
    )
  ) {
    signUp = "可报名";
  } else if (dayjs().isAfter(dayjs(data?.sign_up_end_at))) {
    signUp = "已结束";
  }
  return (
    <>
      <Row>
        <Col flex="auto"></Col>
        <Col flex="960px">
          <Space orientation="vertical">
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
                fallback={`${import.meta.env.BASE_URL}/app/course/img/default.jpg`}
              />
              <Space orientation="vertical">
                <Title level={3}>{data?.title}</Title>
                <Title level={5}>{data?.subtitle}</Title>
                <Text>
                  <Space>
                    {"报名时间:"}
                    {dayjs(data?.sign_up_start_at).format("YYYY-MM-DD")}——
                    {dayjs(data?.sign_up_end_at).format("YYYY-MM-DD")}
                    <Tag>{signUp}</Tag>
                  </Space>
                </Text>
                <Text>
                  <Space>
                    {"课程时间:"}
                    {dayjs(data?.start_at).format("YYYY-MM-DD")}——
                    {dayjs(data?.end_at).format("YYYY-MM-DD")}
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
