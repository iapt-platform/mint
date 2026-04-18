import { Button, Skeleton, Timeline } from "antd";

import TimeShow from "../general/TimeShow";
import { StatusButtons, type TTaskStatus } from "../../api/task";
import { TaskStatusColor } from "./TaskStatus";
import User from "../auth/User";
import { useDiscussion } from "../discussion/hooks/useDiscussion";

interface IWidget {
  taskId?: string;
  onMore?: () => void;
}

function findKeywordInTitle(title?: string): string | undefined {
  if (!title) return undefined;
  for (const keyword of StatusButtons) {
    if (title.includes(keyword)) return keyword;
  }
  return undefined;
}

const TaskLog = ({ taskId, onMore }: IWidget) => {
  const { data: logData, loading } = useDiscussion(taskId);

  return (
    <>
      <Timeline>
        {loading && <Skeleton paragraph={{ rows: 1 }} active avatar />}
        {logData?.rows.map((item, id) => {
          const status = findKeywordInTitle(item.title);
          return (
            <Timeline.Item
              key={id}
              color={TaskStatusColor(status as TTaskStatus)}
              icon={<User {...item.editor} showName={false} />}
            >
              <div>
                <TimeShow
                  showLabel={false}
                  showIcon={false}
                  createdAt={item.created_at}
                />
              </div>
              <div>{item.title}</div>
            </Timeline.Item>
          );
        })}
        {logData && logData.count > 5 && (
          <Timeline.Item>
            <Button type="link" onClick={onMore}>
              更多
            </Button>
          </Timeline.Item>
        )}
      </Timeline>
    </>
  );
};

export default TaskLog;
