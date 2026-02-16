import { useEffect, useState } from "react";
import { Collapse, message } from "antd";

import { get } from "../../request";
import { IArticleResponse } from "../api/Article";
import MdView from "../template/MdView";

const { Panel } = Collapse;

interface IWidget {
  courseId?: string;
  articleId?: string;
  exerciseId?: string;
  mode?: string;
  active?: boolean;
}
const ExerciseAnswerWidget = ({
  courseId,
  articleId,
  exerciseId,
  mode,
  active = false,
}: IWidget) => {
  const [answer, setAnswer] = useState<string>();

  useEffect(() => {
    const url = `/v2/article/${articleId}?mode=${mode}&course=${courseId}&exercise=${exerciseId}&view=answer`;
    get<IArticleResponse>(url).then((json) => {
      console.log("article", json);
      if (json.ok) {
        setAnswer(json.data.html);
      } else {
        message.error(json.message);
      }
    });
  }, [courseId, articleId, exerciseId, mode]);
  return (
    <div>
      <Collapse defaultActiveKey={active ? ["answer"] : []}>
        <Panel header="答案" key="answer">
          <MdView html={answer} />
        </Panel>
      </Collapse>
    </div>
  );
};

export default ExerciseAnswerWidget;
