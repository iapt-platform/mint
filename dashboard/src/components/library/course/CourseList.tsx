//课程列表
import React, { useEffect, useState } from "react";
import { LikeOutlined, MessageOutlined, StarOutlined } from "@ant-design/icons";
import { Avatar, List, message, Space, Typography } from "antd";
import { ICourseListResponse } from "../../api/Course";
import { ICourse } from "../../../pages/library/course/course";
import { Link, useNavigate } from "react-router-dom";
import { API_HOST, get } from "../../../request";

const { Paragraph } = Typography;

interface IWidget {
  type: "open" | "close";
}
const Widget = ({ type }: IWidget) => {
  const [data, setData] = useState<ICourse[]>();
  const navigate = useNavigate();

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
            coverUrl: item.cover,
          };
        });
        setData(course);
      } else {
        message.error(json.message);
      }
    });
  }, []);

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
            <img width={128} alt="logo" src={API_HOST + "/" + item.coverUrl} />
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

export default Widget;
