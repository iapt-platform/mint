import React, { useEffect, useState } from "react";
import { IProjectData, ITaskData } from "../api/task";
import ProjectList, { TView } from "./ProjectList";
import ProjectTask from "./ProjectTask";
import { Button, Card, Modal, Tree } from "antd";
import { ArrowLeftOutlined } from "@ant-design/icons";
import { Key } from "antd/es/table/interface";
import { useIntl } from "react-intl";

interface IModal {
  tiger?: React.ReactNode;
  studioName?: string;
  open?: boolean;
  onClose?: () => void;
  onSelect?: (data: IProjectData | undefined) => void;
  onOk?: (data: ITaskData[] | undefined) => void;
}
export const WorkflowModal = ({
  tiger,
  studioName,
  open,
  onSelect,
  onOk,
  onClose,
}: IModal) => {
  const [openModal, setOpenModal] = useState(open);
  const [data, setData] = useState<ITaskData[]>();

  useEffect(() => setOpenModal(open), [open]);

  const showModal = () => {
    setOpenModal(true);
  };

  const handleOk = () => {
    if (onOk) {
      onOk(data);
    } else {
      setOpenModal(false);
    }
  };

  const handleCancel = () => {
    if (onClose) {
      onClose();
    } else {
      setOpenModal(false);
    }
  };
  return (
    <>
      <div onClick={() => showModal()}>{tiger}</div>
      <Modal
        destroyOnClose={true}
        width={1200}
        style={{ top: 10 }}
        title={""}
        open={openModal}
        onOk={handleOk}
        onCancel={handleCancel}
      >
        <Workflow
          studioName={studioName}
          onData={(data) => setData(data)}
          onSelect={onSelect}
        />
      </Modal>
    </>
  );
};

interface IWidget {
  studioName?: string;
  onSelect?: (data: IProjectData | undefined) => void;
  onData?: (data: ITaskData[] | undefined) => void;
}

const Workflow = ({ studioName, onSelect, onData }: IWidget) => {
  const intl = useIntl();

  const [project, setProject] = useState<IProjectData>();
  const [view, setView] = useState<TView>("studio");

  const selectWorkflow = (selected: IProjectData | undefined) => {
    onSelect && onSelect(selected);
    setProject(selected);
  };
  return (
    <div style={{ display: "flex" }}>
      <div style={{ minWidth: 200, flex: 1 }}>
        <Tree
          multiple={false}
          defaultSelectedKeys={["studio"]}
          treeData={[
            {
              title: intl.formatMessage({ id: "labels.this-studio" }),
              key: "studio",
            },
            {
              title: intl.formatMessage({ id: "labels.shared" }),
              key: "shared",
            },
            {
              title: intl.formatMessage({ id: "labels.community" }),
              key: "community",
            },
          ]}
          onSelect={(selectedKeys: Key[]) => {
            console.debug("selectedKeys", selectedKeys);
            if (selectedKeys.length > 0) {
              selectWorkflow(undefined);
              setView(selectedKeys[0].toString() as TView);
            }
          }}
        />
      </div>
      <div style={{ flex: 5 }}>
        <div style={{ display: project ? "block" : "none" }}>
          <Card
            title={
              project ? (
                <>
                  <Button
                    type="link"
                    icon={<ArrowLeftOutlined />}
                    onClick={() => {
                      selectWorkflow(undefined);
                    }}
                  />
                  {project.title}
                </>
              ) : (
                "请选择一个工作流"
              )
            }
          >
            {project ? (
              <ProjectTask
                studioName={studioName}
                projectId={project.id}
                readonly={view !== "studio"}
                onChange={onData}
              />
            ) : (
              <></>
            )}
          </Card>
        </div>
        <div style={{ display: project ? "none" : "block" }}>
          <ProjectList
            studioName={studioName}
            view={view}
            type="workflow"
            readonly
            onSelect={(data: IProjectData) => selectWorkflow(data)}
          />
        </div>
      </div>
    </div>
  );
};

export default Workflow;
