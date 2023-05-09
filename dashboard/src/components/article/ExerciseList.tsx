import { useEffect, useState } from "react";
import { Collapse, Space, Tag } from "antd";

import { ICourseExerciseResponse } from "../api/Course";
import { get } from "../../request";
import { IUser } from "../auth/User";
import MdView from "../template/MdView";

const { Panel } = Collapse;

interface DataItem {
  sn: number;
  name: string;
  user: IUser;
  wbw: number;
  translation: number;
  question: number;
  html: string;
}
interface IWidget {
  courseId?: string;
  articleId?: string;
  exerciseId?: string;
}
const ExerciseListWidget = ({ courseId, articleId, exerciseId }: IWidget) => {
  const [data, setData] = useState<DataItem[]>();

  useEffect(() => {
    const url = `/v2/exercise?course_id=${courseId}&article_id=${articleId}&exercise_id=${exerciseId}`;
    console.log(url);
    get<ICourseExerciseResponse>(url)
      .then((json) => {
        if (json.ok) {
          console.log(json.data);
          const items: DataItem[] = json.data.rows.map((item, id) => {
            let member: DataItem = {
              sn: id,
              name: item.user.nickName,
              user: item.user,
              wbw: item.wbw,
              translation: item.translation,
              question: item.question,
              html: item.html,
            };
            return member;
          });
          setData(items);
        } else {
          console.error(json.message);
        }
      })
      .catch((error) => {
        console.error(error);
      });
  }, [courseId, articleId, exerciseId]);
  return (
    <>
      <Collapse>
        {data?.map((item, id) => {
          const header = (
            <Space>
              <span>{item.name}</span>
              {item.wbw === 0 ? <></> : <Tag color="blue">wbw-{item.wbw}</Tag>}
              {item.question === 0 ? (
                <></>
              ) : (
                <Tag color="#5BD8A6">Q-{item.question}</Tag>
              )}
            </Space>
          );

          return (
            <Panel header={header} key={id}>
              <MdView html={item.html} />
            </Panel>
          );
        })}
      </Collapse>
    </>
  );
};

export default ExerciseListWidget;
