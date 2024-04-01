import { useState } from "react";
import { useParams } from "react-router-dom";
import { useIntl } from "react-intl";
import { Card, Tabs } from "antd";

import GoBack from "../../../components/studio/GoBack";
import CourseInfoEdit from "../../../components/course/CourseInfoEdit";
import CourseMemberList from "../../../components/course/CourseMemberList";
import CourseMemberTimeLine from "../../../components/course/CourseMemberTimeLine";
import { ICourseMember } from "../../../components/course/CourseMember";

const Widget = () => {
  const intl = useIntl();
  const { studioname, courseId } = useParams(); //url 参数
  const [title, setTitle] = useState("loading");
  const [selected, setSelected] = useState<string>();
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
                <div style={{ display: "flex" }}>
                  <div style={{ flex: 1 }}>
                    <CourseMemberList
                      courseId={courseId}
                      onSelect={(value: ICourseMember) => {
                        setSelected(value.user?.id);
                      }}
                    />
                  </div>
                  <div style={{ flex: 1 }}>
                    <Tabs
                      items={[
                        {
                          key: "timeline",
                          label: "timeline",
                          children:
                            courseId && selected ? (
                              <CourseMemberTimeLine
                                courseId={courseId}
                                userId={selected}
                              />
                            ) : (
                              <>{"未选择"}</>
                            ),
                        },
                      ]}
                    />
                  </div>
                </div>
              ),
            },
          ]}
        />
      </Card>
    </>
  );
};

export default Widget;
