import { useState } from "react";
import { useParams } from "react-router-dom";
import { useIntl } from "react-intl";
import { Card, Tabs } from "antd";

import GoBack from "../../../components/studio/GoBack";
import CourseInfoEdit from "../../../components/course/CourseInfoEdit";
import CourseMemberList from "../../../components/course/CourseMemberList";

const Widget = () => {
  const intl = useIntl();
  const { studioname, courseId } = useParams(); //url 参数
  const [title, setTitle] = useState("loading");

  return (
    <>
      <Card
        title={
          <GoBack to={`/studio/${studioname}/course/list`} title={title} />
        }
      >
        <Tabs
          defaultActiveKey="info"
          items={[
            {
              key: "info",
              label: intl.formatMessage({ id: "course.basic.info.label" }),
              children: (
                <CourseInfoEdit
                  studioName={studioname}
                  courseId={courseId}
                  onTitleChange={(title: string) => {
                    setTitle(title);
                    document.title = `${title}`;
                  }}
                />
              ),
            },
            {
              key: "member",
              label: `成员`,
              children: (
                <CourseMemberList studioName={studioname} courseId={courseId} />
              ),
            },
          ]}
        />
      </Card>
    </>
  );
};

export default Widget;
