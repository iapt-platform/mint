import { Button, Card, Space, Switch } from "antd";
import { Link, useParams } from "react-router-dom";
import { useAppSelector } from "../../hooks";
import { courseUser } from "../../reducers/course-user";
import SelectChannel from "../course/SelectChannel";
import { currentUser as _currentUser } from "../../reducers/current-user";
import { courseInfo } from "../../reducers/current-course";

interface IWidgetExerciseCtl {
  id?: string;
  title?: string;
  channel?: string;
  children?: React.ReactNode;
}
const ExerciseCtl = ({ id, title, channel, children }: IWidgetExerciseCtl) => {
  const { type } = useParams(); //url 参数

  const cardTitle = title ? title : "练习";
  const user = useAppSelector(courseUser);
  const currUser = useAppSelector(_currentUser);
  const currCourse = useAppSelector(courseInfo);

  let exeButton = <></>;
  switch (user?.role) {
    case "student":
      switch (type) {
        case "textbook":
          if (typeof user.channelId === "string") {
            exeButton = (
              <Link
                to={`/article/exercise/${currCourse?.courseId}_${currCourse?.articleId}_${id}_${currUser?.realName}/wbw`}
                target="_blank"
              >
                <Button type="primary">做练习</Button>
              </Link>
            );
          } else {
            exeButton = <SelectChannel exerciseId={id} channel={channel} />;
          }
          break;
        default:
          exeButton = (
            <Space>
              问题筛选
              <Switch
                onChange={(checked: boolean) => {
                  console.log(`switch to ${checked}`);
                }}
              />
              对答案
              <Switch
                onChange={(checked: boolean) => {
                  console.log(`switch to ${checked}`);
                }}
              />
            </Space>
          );
          break;
      }

      break;
    case "assistant":
      if (type === "textbook") {
        exeButton = (
          <Link
            to={`/article/exercise-list/${currCourse?.courseId}_${currCourse?.articleId}_${id}/wbw`}
            target="_blank"
          >
            <Button type="primary">查看作业列表</Button>
          </Link>
        );
      }
      break;
    default:
      break;
  }
  return (
    <Card
      title={cardTitle}
      extra={exeButton}
      style={{ backgroundColor: "beige" }}
    >
      {children}
    </Card>
  );
};

interface IWidget {
  props: string;
  children?: React.ReactNode;
}
const Widget = ({ props, children }: IWidget) => {
  const prop: IWidgetExerciseCtl = JSON.parse(atob(props));
  return <ExerciseCtl {...prop}>{children}</ExerciseCtl>;
};

export default Widget;
