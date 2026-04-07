import { useState } from "react";
import { useParams } from "react-router";
import { useIntl } from "react-intl";
import { Card, Tabs } from "antd";

import CourseInfoEdit from "../../../components/course/CourseInfoEdit";
import CourseMemberList, {
  type ICourseMember,
} from "../../../components/course/CourseMemberList";
import CourseMemberTimeLine from "../../../components/course/CourseMemberTimeLine";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";

const Widget = () => {
  const intl = useIntl();
  const { courseId } = useParams(); //url 参数
  const user = useAppSelector(currentUser);
  const studioName = user?.realName;
  const [title, setTitle] = useState("loading");
  const [selected, setSelected] = useState<string>();
  return (
    <>
      <title>{title}</title>
      <Card>
        <Tabs
          defaultActiveKey="info"
          items={[
            {
              key: "info",
              label: intl.formatMessage({ id: "course.basic.info.label" }),
              children: (
                <CourseInfoEdit
                  studioName={studioName}
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
              label: intl.formatMessage({
                id: "auth.role.member",
              }),
              children: (
                <div style={{ display: "flex" }}>
                  <div style={{ flex: 3 }}>
                    <CourseMemberList
                      courseId={courseId}
                      onSelect={(value: ICourseMember) => {
                        setSelected(value.user?.id);
                      }}
                    />
                  </div>
                  <div style={{ flex: 2 }}>
                    <Tabs
                      items={[
                        {
                          key: "timeline",
                          label: intl.formatMessage({
                            id: "course.member.timeline",
                          }),
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
