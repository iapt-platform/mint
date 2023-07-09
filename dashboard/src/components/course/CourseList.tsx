//课程列表
import { Link } from "react-router-dom";
import { useEffect, useState } from "react";

import { Avatar, List, message, Typography, Image } from "antd";
import { ICourse } from "../../pages/library/course/course";
import { ICourseListResponse } from "../api/Course";
import { API_HOST, get } from "../../request";

const { Paragraph } = Typography;

interface IWidget {
  type: "open" | "close";
}
const CourseListWidget = ({ type }: IWidget) => {
  const [data, setData] = useState<ICourse[]>();

  useEffect(() => {
    get<ICourseListResponse>(`/v2/course?view=${type}`).then((json) => {
      if (json.ok) {
        console.log(json.data);
        const course: ICourse[] = json.data.rows.map((item) => {
          return {
            id: item.id,
            title: item.title,
            subtitle: item.subtitle,
            teacher: item.teacher,
            intro: item.content,
            coverUrl: item.cover_url,
          };
        });
        setData(course);
      } else {
        message.error(json.message);
      }
    });
  }, [type]);

  return (
    <List
      itemLayout="vertical"
      size="default"
      pagination={{
        onChange: (page) => {
          console.log(page);
        },
        pageSize: 5,
      }}
      dataSource={data}
      renderItem={(item) => (
        <List.Item
          key={item.title}
          extra={
            <Image
              width={128}
              style={{ borderRadius: 12 }}
              src={
                item.coverUrl && item.coverUrl.length > 1
                  ? item.coverUrl[1]
                  : undefined
              }
              preview={{
                src:
                  item.coverUrl && item.coverUrl.length > 0
                    ? item.coverUrl[0]
                    : undefined,
              }}
              fallback={`${API_HOST}/app/course/img/default.jpg`}
            />
          }
        >
          <List.Item.Meta
            avatar={<Avatar />}
            title={<Link to={`/course/show/${item.id}`}>{item.title}</Link>}
            description={<div>主讲：{item.teacher?.nickName}</div>}
          />
          <Paragraph ellipsis={{ rows: 2, expandable: false }}>
            {item.intro}
          </Paragraph>
        </List.Item>
      )}
    />
  );
};

export default CourseListWidget;
