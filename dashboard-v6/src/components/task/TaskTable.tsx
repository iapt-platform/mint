import { useMemo } from "react";

import type { IProject, ITaskData } from "../../api/task";
import "../article/article.css";
import TaskTableCell from "./TaskTableCell";

interface ITaskHeading {
  id: string;
  title: string;
  children: number;
}

interface IWidget {
  tasks?: ITaskData[];
  onChange?: (treeData: ITaskData[]) => void;
}

const TaskTable = ({ tasks, onChange }: IWidget) => {
  const projects = useMemo<IProject[]>(() => {
    const projectsId = new Map<string, number>();
    const projectMap = new Map<string, IProject>();
    tasks?.forEach((task) => {
      if (task.project_id && task.project) {
        if (projectsId.has(task.project_id)) {
          projectsId.set(task.project_id, projectsId.get(task.project_id)! + 1);
        } else {
          projectsId.set(task.project_id, 1);
          projectMap.set(task.project_id, task.project);
        }
      }
    });
    return Array.from(projectMap.values());
  }, [tasks]);

  const tasksTitle = useMemo<ITaskHeading[][]>(() => {
    const getNodeChildren = (task: ITaskData): number => {
      const children = tasks?.filter((value) => value.parent_id === task.id);
      if (children && children.length > 0) {
        return children.reduce(
          (acc, cur) => acc + getNodeChildren(cur),
          children.length
        );
      }
      return 0;
    };

    const titles1: ITaskHeading[] = [];
    let titles2: ITaskHeading[] = [];
    const tRoot = new Map<string, ITaskData>();

    tasks
      ?.filter((value: ITaskData) => !value.parent_id)
      .forEach((task) => {
        tRoot.set(task.title, task);
      });

    tRoot.forEach((task) => {
      const children = tasks
        ?.filter((value1) => value1.parent_id === task.id)
        .map(
          (task1): ITaskHeading => ({
            id: task1.id,
            title: task1.title ?? "",
            children: 0,
          })
        );

      if (children) {
        titles2 = [...titles2, ...children];
      }

      titles1.push({
        title: task.title ?? "",
        id: task.id,
        children: getNodeChildren(task),
      });
    });

    return [titles1, titles2];
  }, [tasks]);

  const dataHeading = useMemo<string[]>(() => {
    const tRoot = new Map<string, ITaskData>();
    tasks
      ?.filter((value: ITaskData) => !value.parent_id)
      .forEach((task) => {
        tRoot.set(task.title, task);
      });

    let titles3: string[] = [];
    tRoot.forEach((task) => {
      const children = tasks?.filter((value1) => value1.parent_id === task.id);
      if (children && children.length > 0) {
        titles3 = [...titles3, ...children.map((item) => item.title ?? "")];
      } else {
        titles3.push(task.title ?? "");
      }
    });
    return titles3;
  }, [tasks]);

  return (
    <div className="pcd_article">
      <table>
        <thead>
          {tasksTitle?.map((row, level) => (
            <tr key={level}>
              {level === 0 ? (
                <>
                  <th rowSpan={2}>project</th>
                  <th>weight</th>
                </>
              ) : undefined}
              {row.map((task, index) => (
                <th
                  key={index}
                  colSpan={task.children === 0 ? undefined : task.children}
                  rowSpan={task.children === 0 ? 2 : undefined}
                >
                  {task.title}
                </th>
              ))}
            </tr>
          ))}
        </thead>
        <tbody>
          {projects
            ?.sort((a, b) => a.sn - b.sn)
            .map((row, index) => (
              <tr key={index}>
                <td>{row.title}</td>
                <td>{row.weight}</td>
                {dataHeading?.map((task, id) => {
                  const taskData = tasks?.find(
                    (value: ITaskData) =>
                      value.title === task && value.project_id === row.id
                  );
                  return (
                    <td key={id}>
                      <TaskTableCell task={taskData} onChange={onChange} />
                    </td>
                  );
                })}
              </tr>
            ))}
        </tbody>
      </table>
    </div>
  );
};

export default TaskTable;
