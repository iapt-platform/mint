//主讲人列表
import { useNavigate } from "react-router-dom";
import { useEffect, useState } from "react";
import { Card, List, message, Typography, Image } from "antd";

import { ICourseDataResponse, ICourseListResponse } from "../api/Course";
import { API_HOST, get } from "../../request";
import CourseNewLoading from "./CourseNewLoading";

const { Meta } = Card;
const { Paragraph } = Typography;

const LecturerListWidget = () => {
  const [data, setData] = useState<ICourseDataResponse[]>();
  const [loading, setLoading] = useState(false);

  const navigate = useNavigate();

  useEffect(() => {
    const url = `/v2/course?view=new&limit=4`;
    console.info("course url", url);
    setLoading(true);
    get<ICourseListResponse>(url)
      .then((json) => {
        if (json.ok) {
          console.log(json.data);
          setData(json.data.rows);
        } else {
          message.error(json.message);
        }
      })
      .finally(() => setLoading(false));
  }, []);
  return loading ? (
    <CourseNewLoading />
  ) : (
    <List
      grid={{ gutter: 16, column: 4 }}
      dataSource={data}
      renderItem={(item) => (
        <List.Item>
          <Card
            hoverable
            style={{ width: "100%", height: 350 }}
            cover={
              <Image
                alt="example"
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
                width="240"
                height="200"
                fallback={`${API_HOST}/app/course/img/default.jpg`}
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
                  {item.summary}
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
