// 任务详情页面
import { useMatches, useParams } from "react-router";
import { useIntl } from "react-intl";

import Task from "../../../components/task/Task";

const Widget = () => {
  const { taskId } = useParams();
  const intl = useIntl();
  const matches = useMatches() as {
    data?: { title?: string; name?: string; word?: string };
  }[];
  const data = [...matches].reverse().find((m) => m.data)?.data;
  const name = data?.title ?? data?.name ?? data?.word;
  const prefix = intl.formatMessage({ id: "pages.task.show.title" });

  return (
    <>
      <title>{name ? `${prefix}-${name}` : prefix}</title>
      <div style={{ maxWidth: 1000, margin: "0 auto" }}>
        <Task taskId={taskId} />
      </div>
    </>
  );
};

export default Widget;
