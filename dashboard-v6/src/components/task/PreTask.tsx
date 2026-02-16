import { Button, List, Popover, Switch, Tag, Typography } from "antd";
import { ITaskData, ITaskListResponse } from "../api/task";
import { get } from "../../request";
import { useEffect, useState } from "react";
import { ArrowLeftOutlined, ArrowRightOutlined } from "@ant-design/icons";
import { TRelation } from "./TaskEditButton";

const { Text } = Typography;

interface IProTaskListProps {
  task?: ITaskData;
  type: TRelation;
  onClick?: (data?: ITaskData | null) => void;
  onClose?: () => void;
  onChange?: (data: ITaskData, has: boolean) => void;
}
const ProTaskList = ({
  task,
  type,
  onClick,
  onClose,
  onChange,
}: IProTaskListProps) => {
  const [res, setRes] = useState<ITaskData[]>();
  useEffect(() => {
    const url = `/v2/task?view=project&project_id=${task?.project_id}`;

    console.info("api request", url);
    get<ITaskListResponse>(url).then((json) => {
      console.info("project api response", json);
      const res = json.data.rows;
      setRes(res);
    });
  }, [task?.project_id]);

  return (
    <List
      header={
        <div>
          <div style={{ display: "flex", justifyContent: "space-between" }}>
            <Text strong>{type === "pre" ? "前置任务" : "后置任务"}</Text>
            <div>
              <Button type="link" onClick={onClose}>
                关闭
              </Button>
            </div>
          </div>
        </div>
      }
      footer={false}
      dataSource={res}
      renderItem={(item) => {
        let checked = false;
        if (type === "pre") {
          checked =
            task?.pre_task?.find((value) => value.id === item.id) !== undefined;
        } else {
          checked =
            task?.next_task?.find((value) => value.id === item.id) !==
            undefined;
        }
        return (
          <List.Item
            actions={[
              <Switch
                size="small"
                checked={checked}
                onChange={(checked) => {
                  onChange && onChange(item, checked);
                }}
              />,
            ]}
            onClick={() => {
              onClick && onClick(item);
            }}
          >
            {item.title}
          </List.Item>
        );
      }}
    />
  );
};

interface IWidget {
  task?: ITaskData;
  open?: boolean;
  type: TRelation;
  onClick?: (data?: ITaskData | null) => void;
  onTagClick?: () => void;
  onClose?: () => void;
  onChange?: (data: ITaskData, has: boolean) => void;
}
const PreTask = ({
  task,
  type,
  open = false,
  onClick,
  onClose,
  onTagClick,
  onChange,
}: IWidget) => {
  const preTaskShow = open || task?.pre_task;
  const nextTaskShow = open || task?.next_task;
  let tag = <></>;
  if (preTaskShow && type === "pre") {
    tag = (
      <Tag color="warning" icon={<ArrowLeftOutlined />} onClick={onTagClick}>
        {task?.pre_task ? `${task?.pre_task?.length} 个前置任务` : ""}
      </Tag>
    );
  } else if (nextTaskShow && type === "next") {
    tag = (
      <Tag color="warning" icon={<ArrowRightOutlined />} onClick={onTagClick}>
        {task?.next_task ? `阻塞 ${task?.next_task?.length} 个任务` : ""}
      </Tag>
    );
  }
  return (
    <Popover
      trigger="click"
      open={open}
      content={
        <div style={{ width: 400 }}>
          <ProTaskList
            type={type}
            task={task}
            onClick={onClick}
            onClose={onClose}
            onChange={onChange}
          />
        </div>
      }
    >
      {tag}
    </Popover>
  );
};
export default PreTask;
