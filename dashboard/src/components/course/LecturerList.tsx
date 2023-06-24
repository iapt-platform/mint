//主讲人列表
import { useNavigate } from "react-router-dom";
import { useEffect, useState } from "react";
import { Card, List, message, Typography } from "antd";

import { ICourse } from "../../pages/library/course/course";
import { ICourseListResponse } from "../api/Course";
import { API_HOST, get } from "../../request";

const { Meta } = Card;
const { Paragraph } = Typography;

const LecturerListWidget = () => {
  const [data, setData] = useState<ICourse[]>();
  const navigate = useNavigate();

  useEffect(() => {
    get<ICourseListResponse>(`/v2/course?view=new&limit=4`).then((json) => {
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
      grid={{ gutter: 16, column: 4 }}
      dataSource={data}
      renderItem={(item) => (
        <List.Item>
          <Card
            hoverable
            style={{ width: "100%", height: 300 }}
            cover={
              <img
                alt="example"
                src={API_HOST + "/" + item.coverUrl}
                width="240"
                height="200"
              />
            }
            onClick={(e) => {
              navigate(`/course/show/${item.id}`);
            }}
          >
            <Meta
              title={item.title}
              description={
                <Paragraph ellipsis={{ rows: 2, expandable: false }}>
                  {item.intro}
                </Paragraph>
              }
            />
          </Card>
        </List.Item>
      )}
    />
  );
};
export default LecturerListWidget;
