//课程列表
import { Link } from "react-router-dom";
import { useEffect, useState } from "react";
import { Avatar, List, message, Typography, Image } from "antd";

import { ICourseDataResponse, ICourseListResponse } from "../api/Course";
import { API_HOST, get } from "../../request";

const { Paragraph } = Typography;

interface IWidget {
  type: "open" | "close";
}
const CourseListWidget = ({ type }: IWidget) => {
  const [data, setData] = useState<ICourseDataResponse[]>();

  useEffect(() => {
    get<ICourseListResponse>(`/v2/course?view=${type}`).then((json) => {
      if (json.ok) {
        console.log(json.data);
        setData(json.data.rows);
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
                item.cover_url && item.cover_url.length > 1
                  ? item.cover_url[1]
                  : undefined
              }
              preview={{
                src:
                  item.cover_url && item.cover_url.length > 0
                    ? item.cover_url[0]
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
            {item.summary}
          </Paragraph>
        </List.Item>
      )}
    />
  );
};

export default CourseListWidget;
