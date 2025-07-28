import { Button, Timeline } from "antd";
import React, { useEffect, useState } from "react";
import { get } from "../../request";
import { ICommentApiData, ICommentListResponse } from "../api/Comment";
import TimeShow from "../general/TimeShow";
import { StatusButtons, TTaskStatus } from "../api/task";
import { TaskStatusColor } from "./TaskStatus";
import User from "../auth/User";

interface IWidget {
  taskId?: string;
  onMore?: () => void;
}
const TaskLog = ({ taskId, onMore }: IWidget) => {
  const [data, setData] = useState<ICommentApiData[]>();
  useEffect(() => {
    const url: string = `/v2/discussion?type=discussion&res_type=task&view=question&id=${taskId}&limit=5&offset=0&status=active`;
    console.info("api request", url);
    get<ICommentListResponse>(url).then((json) => {
      if (json.ok) {
        console.debug("discussion api response", json);
        setData(json.data.rows);
      }
    });
  }, [taskId]);

  function findKeywordInTitle(title?: string): string | undefined {
    if (!title) {
      return undefined;
    }
    const keywords = StatusButtons;

    for (const keyword of keywords) {
      if (title.includes(keyword)) {
        return keyword;
      }
    }

    return undefined;
  }

  return (
    <>
      <Timeline>
        {data?.map((item, id) => {
          const status = findKeywordInTitle(item.title);
          return (
            <Timeline.Item
              key={id}
              color={TaskStatusColor(status as TTaskStatus)}
              dot={<User {...item.editor} showName={false} />}
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
        <Timeline.Item>
          <Button type="link" onClick={onMore}>
            更多
          </Button>
        </Timeline.Item>
      </Timeline>
    </>
  );
};

export default TaskLog;
