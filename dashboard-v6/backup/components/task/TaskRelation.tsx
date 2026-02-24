import { Collapse } from "antd";
import type { IProject, ITaskData } from "../../api/task";
import TaskFlowchart from "./TaskFlowchart";

const { Panel } = Collapse;

interface IWidget {
  projectId?: string;
  tasks?: ITaskData[];
}
const TaskRelation = ({ tasks }: IWidget) => {
  const projects = new Map<string, IProject>();
  tasks?.forEach((value) => {
    value.project && projects.set(value.project.id, value.project);
  });
  const flowcharts: IProject[] = [];
  projects.forEach((value: IProject, _key: string) => {
    flowcharts.push(value);
  });

  return (
    <Collapse
      defaultActiveKey={Array.from({ length: flowcharts.length }, (_, i) => i)}
    >
      {flowcharts
        .sort((a, b) => a.sn - b.sn)
        .map((item, id) => {
          return (
            <Panel header={item.title} key={id}>
              <TaskFlowchart
                tasks={tasks?.filter((value) => value.project_id === item.id)}
              />
            </Panel>
          );
        })}
    </Collapse>
  );
};

export default TaskRelation;
