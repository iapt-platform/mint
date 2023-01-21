import { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { Card, Col, Row } from "antd";

import GoBack from "../../../components/studio/GoBack";
import { ProForm } from "@ant-design/pro-components";
import LessonTreeShow from "../../../components/library/course/LessonTreeShow";
import StudentsSelect from "../../../components/library/course/StudentsSelect";

interface IFormData {
  uid: string;
  title: string;

  t_type: string;
  status: number;
  lang: string;
}

const Widget = () => {
  const { studioname, courseId } = useParams(); //url 参数
  const [title, setTitle] = useState("loading");
  useEffect(() => {
    setTitle("title");
  }, [courseId]);
  return (
    <Card
      title={<GoBack to={`/studio/${studioname}/course/list`} title={title} />}
    >
      <ProForm<IFormData> onFinish={async (values: IFormData) => {}}>
        <ProForm.Group>
          <LessonTreeShow />
        </ProForm.Group>
        <ProForm.Group></ProForm.Group>

        <Row>
          <Col flex="400px">
            <StudentsSelect />
          </Col>
        </Row>
      </ProForm>
    </Card>
  );
};

export default Widget;
