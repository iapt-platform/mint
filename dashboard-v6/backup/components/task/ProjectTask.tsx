import { Tabs } from "antd";

import TaskList, { treeToList } from "./TaskList";
import TaskTable from "./TaskTable";
import TaskRelation from "./TaskRelation";
import { useState } from "react";
import type { ITaskData } from "../../api/task";
import { useIntl } from "react-intl";

// 更新 ITaskData[] 中的函数
export function update(input: ITaskData[], target: ITaskData[]): void {
  for (const newItem of input) {
    const match = target.findIndex((item) => item.id === newItem.id);
    if (match >= 0) {
      // 更新当前项的属性
      target[match] = newItem;
    } else {
      // 如果没有找到，递归检查子项
      for (const item of target) {
        if (item.children) {
          update([newItem], item.children);
        }
      }
    }
  }
}
// 更新函数
export function updateNode(tree: ITaskData[], changed: ITaskData): boolean {
  for (let i = 0; i < tree.length; i++) {
    if (tree[i].id === changed.id) {
      tree[i] = { ...tree[i], ...changed };
      return true;
    }
    if (tree[i].children) {
      const updated = updateNode(tree[i].children!, changed);
      updated && console.debug("TaskList children", tree[i].children);
      if (updated) return true;
    }
  }
  return false;
}
interface IWidget {
  studioName?: string;
  projectId?: string;
  readonly?: boolean;
  onChange?: (data: ITaskData[]) => void;
}
const ProjectTask = ({
  studioName,
  projectId,
  readonly = false,
  onChange,
}: IWidget) => {
  const [tasks, setTasks] = useState<ITaskData[]>([]);
  const [taskTree, setTaskTree] = useState<ITaskData[]>();
  const intl = useIntl();

  const onDataChange = (treeData: ITaskData[]) => {
    setTaskTree(treeData);
    const listData = treeToList(treeData);
    setTasks(listData);
    onChange && onChange(listData);
  };

  return (
    <>
      <Tabs
        type="card"
        items={[
          {
            label: intl.formatMessage({ id: "labels.list" }),
            key: "list",
            children: (
              <TaskList
                editable={!readonly}
                studioName={studioName}
                projectId={projectId}
                taskTree={taskTree}
                onChange={onDataChange}
              />
            ),
          },
          {
            label: intl.formatMessage({ id: "labels.table" }),
            key: "table",
            children: (
              <TaskTable
                tasks={tasks}
                onChange={(data: ITaskData[]) => {
                  if (origin) {
                    const origin = JSON.parse(JSON.stringify(taskTree));
                    update(data, origin);
                    onDataChange(origin);
                  }
                }}
              />
            ),
          },
          {
            label: intl.formatMessage({ id: "labels.flowchart" }),
            key: "flowchart",
            children: <TaskRelation tasks={tasks} />,
          },
        ]}
      ></Tabs>
    </>
  );
};

export default ProjectTask;
