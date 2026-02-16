import {
  Button,
  Divider,
  Input,
  message,
  Modal,
  Space,
  Steps,
  Tag,
  Typography,
} from "antd";

import { useState } from "react";
import Workflow from "./Workflow";
import {
  IProjectTreeInsertRequest,
  IProjectTreeResponse,
  IProjectUpdateRequest,
  ITaskData,
  ITaskGroupInsertData,
  ITaskGroupInsertRequest,
  ITaskGroupResponse,
} from "../api/task";

import { post } from "../../request";
import TaskBuilderProp, { IParam, IProp } from "./TaskBuilderProp";

const { Paragraph, Text } = Typography;

interface IBuildProjects {
  onChange?: (titles: string[]) => void;
}
const BuildProjects = ({ onChange }: IBuildProjects) => {
  const [projectsTitle, setProjectsTitle] = useState<string[]>([]);
  const [projectTitle, setProjectTitle] = useState<string>("");
  const [projectTitlePerf, setProjectTitlePerf] = useState<string>("01");
  const [projectsCount, setProjectsCount] = useState<number>(0);

  const buildTitles = (base: string, perf: string, count: number): string[] => {
    return Array.from(Array(count).keys()).map((item) => {
      const sn = parseInt(perf) + item;
      const extraZero = perf.length - sn.toString().length;
      let strSn: string = sn.toString();
      if (extraZero > 0) {
        strSn = Array(extraZero).fill("0").join("") + sn.toString();
      }
      return `${base}${strSn}`;
    });
  };

  return (
    <div>
      <div>
        名称：
        <Input
          onChange={(e) => {
            setProjectTitle(e.target.value);
            const projects = buildTitles(
              e.target.value,
              projectTitlePerf,
              projectsCount
            );
            setProjectsTitle(projects);
            onChange && onChange(projects);
          }}
        />
      </div>
      <div>
        后缀：
        <Input
          defaultValue={projectTitlePerf}
          onChange={(e) => {
            setProjectTitlePerf(e.target.value);
            const projects = buildTitles(
              projectTitle,
              e.target.value,
              projectsCount
            );
            setProjectsTitle(projects);
            onChange && onChange(projects);
          }}
        />
      </div>
      <div>
        数量：
        <Input
          onChange={(e) => {
            setProjectsCount(parseInt(e.target.value));
            const projects = buildTitles(
              projectTitle,
              projectTitlePerf,
              parseInt(e.target.value)
            );
            setProjectsTitle(projects);
            onChange && onChange(projects);
          }}
        />
      </div>
      <div style={{ overflowY: "scroll", height: 370 }}>
        {projectsTitle.map((item, id) => {
          return (
            <Paragraph key={id}>
              <Tag>{`${id + 1}`}</Tag>
              {item}
            </Paragraph>
          );
        })}
      </div>
    </div>
  );
};

interface IModal {
  studioName?: string;
  parentId?: string;

  open?: boolean;
  onClose?: () => void;
  onDone?: () => void;
}
export const TaskBuilderProjectsModal = ({
  studioName,
  parentId,
  open = false,
  onClose,
  onDone,
}: IModal) => {
  return (
    <>
      <Modal
        destroyOnClose={true}
        width={1400}
        style={{ top: 10 }}
        title={""}
        footer={false}
        open={open}
        onOk={onClose}
        onCancel={onClose}
      >
        <TaskBuilderProjects
          style={{ marginTop: 20 }}
          studioName={studioName}
          parentId={parentId}
          onDone={onDone}
        />
      </Modal>
    </>
  );
};

interface IWidget {
  studioName?: string;
  parentId?: string;
  style?: React.CSSProperties;
  onDone?: () => void;
}
const TaskBuilderProjects = ({
  studioName,
  parentId,
  style,
  onDone,
}: IWidget) => {
  const [current, setCurrent] = useState(0);
  const [workflow, setWorkflow] = useState<ITaskData[]>();
  const [projectsTitle, setProjectsTitle] = useState<string[]>();
  const [prop, setProp] = useState<IProp[]>();
  const [loading, setLoading] = useState(false);
  const [messages, setMessages] = useState<string[]>([]);
  const steps = [
    {
      title: "Projects",
      content: <BuildProjects onChange={setProjectsTitle} />,
    },
    {
      title: "工作流",
      content: (
        <Workflow
          studioName={studioName}
          onData={(data) => setWorkflow(data)}
        />
      ),
    },
    {
      title: "参数设置",
      content: (
        <div>
          <TaskBuilderProp
            workflow={workflow}
            onChange={(data: IProp[] | undefined) => setProp(data)}
          />
        </div>
      ),
    },
    {
      title: "生成",
      content: (
        <div>
          <div>
            <Space>
              <Text type="secondary">title</Text>
              <Text>{projectsTitle}</Text>
            </Space>
          </div>
          <div>
            <Paragraph>新增任务组：{projectsTitle}</Paragraph>
            <Paragraph>每个任务组任务数量：{workflow?.length}</Paragraph>
            <Paragraph>点击生成按钮生成</Paragraph>
          </div>
          <div>
            {messages?.map((item, id) => {
              return <div key={id}>{item}</div>;
            })}
          </div>
        </div>
      ),
    },
  ];

  const next = () => {
    setCurrent(current + 1);
  };

  const prev = () => {
    setCurrent(current - 1);
  };
  const items = steps.map((item) => ({ key: item.title, title: item.title }));

  return (
    <div style={style}>
      <Steps current={current} items={items} />
      <div className="steps-content" style={{ minHeight: 400 }}>
        {steps[current].content}
      </div>
      <Divider></Divider>
      <div style={{ display: "flex", justifyContent: "space-between" }}>
        <Button
          style={{ margin: "0 8px" }}
          disabled={current === 0}
          onClick={() => prev()}
        >
          Previous
        </Button>

        {current < steps.length - 1 && (
          <Button type="primary" onClick={() => next()}>
            Next
          </Button>
        )}
        {current === steps.length - 1 && (
          <Button
            type="primary"
            loading={loading}
            disabled={loading}
            onClick={async () => {
              if (!studioName || !parentId || !projectsTitle) {
                console.error("缺少参数", studioName, parentId, projectsTitle);
                return;
              }
              setLoading(true);
              //生成projects
              setMessages((origin) => [...origin, "正在生成任务组……"]);
              const url = "/v2/project-tree";
              const values: IProjectTreeInsertRequest = {
                studio_name: studioName,
                parent_id: parentId,
                data: projectsTitle.map((item, id) => {
                  const project: IProjectUpdateRequest = {
                    id: item,
                    title: item,
                    type: "instance",
                    parent_id: null,
                    res_id: id.toString(),
                  };
                  return project;
                }),
              };
              console.info("api request", url, values);
              const res = await post<
                IProjectTreeInsertRequest,
                IProjectTreeResponse
              >(url, values);
              console.info("api response", res);
              if (!res.ok) {
                setMessages((origin) => [...origin, "正在生成任务组失败"]);
                return;
              } else {
                setMessages((origin) => [...origin, "生成任务组成功"]);
              }
              //生成tasks
              setMessages((origin) => [...origin, "正在生成任务……"]);
              const taskUrl = "/v2/task-group";
              if (!workflow) {
                return;
              }
              console.debug("prop", prop);
              const taskData: ITaskGroupInsertData[] = res.data.rows
                .filter((value) => value.isLeaf)
                .map((project, pId) => {
                  return {
                    project_id: project.id,
                    tasks: workflow.map((task, tId) => {
                      let newContent = task.description;
                      prop
                        ?.find((pValue) => pValue.taskId === task.id)
                        ?.param?.forEach((value: IParam) => {
                          const searchValue = `${value.key}=${value.value}`;
                          const replaceValue =
                            `${value.key}=` +
                            (value.initValue + value.step * pId).toString();
                          newContent = newContent?.replace(
                            searchValue,
                            replaceValue
                          );
                        });
                      console.debug("description", newContent);
                      return {
                        ...task,
                        type: "instance",
                        description: newContent,
                      };
                    }),
                  };
                });

              console.info("api request", taskUrl, taskData);
              const taskRes = await post<
                ITaskGroupInsertRequest,
                ITaskGroupResponse
              >(taskUrl, { data: taskData });
              console.info("api response", taskRes);
              if (taskRes.ok) {
                message.success("ok");
                setMessages((origin) => [...origin, "生成任务成功."]);
                setMessages((origin) => [
                  ...origin,
                  "生成任务" + taskRes.data.taskCount,
                ]);
                setMessages((origin) => [
                  ...origin,
                  "生成任务关联" + taskRes.data.taskRelationCount,
                ]);
                setMessages((origin) => [
                  ...origin,
                  "打开译经楼-我的任务查看已经生成的任务",
                ]);
                onDone && onDone();
              } else {
                setMessages((origin) => [
                  ...origin,
                  "生成任务失败。错误信息：" + taskRes.data,
                ]);
              }
              setLoading(false);
            }}
          >
            Done
          </Button>
        )}
      </div>
    </div>
  );
};

export default TaskBuilderProjects;
