import type { ITaskData } from "../../api/task";
import Mermaid from "../general/Mermaid";

interface IWidget {
  tasks?: ITaskData[];
}
const TaskFlowchart = ({ tasks }: IWidget) => {
  let mermaidText = "flowchart LR\n";

  //节点样式
  const color = [
    {
      status: "pending",
      fill: "#fafafa",
      stroke: "#d9d9d9",
      color: "#000000d9",
    },
    {
      status: "published",
      fill: "#fff7e6",
      stroke: "#ffd591",
      color: "#d46b08",
    },
    { status: "running", fill: "#e6f7ff", stroke: "#91d5ff", color: "#1890ff" },
    { status: "done", fill: "#f6ffed", stroke: "#b7eb8f", color: "#52c41a" },
    {
      status: "restarted",
      fill: "r#fff2f0",
      stroke: "#ffccc7",
      color: "#ff4d4f",
    },
    {
      status: "requested_restart",
      fill: "#fffbe6",
      stroke: "#ffe58f",
      color: "#faad14",
    },
    { status: "closed", fill: "yellow", stroke: "#333", color: "#333" },
    { status: "canceled", fill: "gray", stroke: "#333", color: "#333" },
    { status: "expired", fill: "brown", stroke: "#333", color: "#333" },
  ];

  color.forEach((value) => {
    mermaidText += `classDef ${value.status} fill:${value.fill},stroke:${value.stroke},color:${value.color},stroke-width:1px;\n`;
  });

  const relationLine = new Map<string, number>();
  tasks?.forEach((task: ITaskData, _index: number, array: ITaskData[]) => {
    //输出节点
    mermaidText += `${task.id}[${task.title}]:::${task.status};\n`;

    //输出带有子任务的节点
    const children = array.filter((value: ITaskData) => {
      return value.parent_id === task.id;
    });
    if (children.length > 0) {
      mermaidText += `subgraph ${task.id} ["${task.title}"]\n`;
      mermaidText += `${children.map((task) => task.id).join(`;\n`)}`;
      mermaidText += ";\nend\n";
    }

    //关系线
    task.pre_task?.map((item) =>
      relationLine.set(`${item.id} --> ${task.id};\n`, 0)
    );
    task.next_task?.map((item) =>
      relationLine.set(`${task.id} --> ${item.id};\n`, 0)
    );
  });

  Array.from(relationLine.keys()).forEach((value) => {
    mermaidText += value;
  });

  console.debug(mermaidText);

  return (
    <div>
      <Mermaid text={mermaidText} />
    </div>
  );
};

export default TaskFlowchart;
