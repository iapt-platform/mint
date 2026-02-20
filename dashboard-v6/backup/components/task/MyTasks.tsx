import { Tabs } from "antd";
import React, { useRef, useState } from "react";
import TaskList from "./TaskList";
import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";

type TargetKey = React.MouseEvent | React.KeyboardEvent | string;

const TaskRunning = ({ studioName }: { studioName?: string }) => {
  const currUser = useAppSelector(currentUser);
  return currUser ? (
    <TaskList
      studioName={studioName}
      status={["running", "restarted"]}
      filters={[
        { field: "executor_id", operator: "includes", value: [currUser?.id] },
      ]}
    />
  ) : (
    <>未登录</>
  );
};
const TaskAssignee = ({ studioName }: { studioName?: string }) => {
  const currUser = useAppSelector(currentUser);
  return currUser ? (
    <TaskList
      studioName={studioName}
      status={["published"]}
      filters={[
        { field: "assignees_id", operator: "includes", value: [currUser?.id] },
      ]}
    />
  ) : (
    <>未登录</>
  );
};
const TaskDone = ({ studioName }: { studioName?: string }) => {
  const currUser = useAppSelector(currentUser);
  return currUser ? (
    <TaskList
      studioName={studioName}
      status={["done"]}
      filters={[
        { field: "executor_id", operator: "includes", value: [currUser?.id] },
      ]}
    />
  ) : (
    <>未登录</>
  );
};

const TaskNew = ({ studioName }: { studioName?: string }) => {
  const currUser = useAppSelector(currentUser);
  return currUser ? (
    <TaskList
      studioName={studioName}
      filters={[
        { field: "executor_id", operator: "includes", value: [currUser?.id] },
      ]}
    />
  ) : (
    <>未登录</>
  );
};

interface IWidget {
  studioName?: string;
}
const MyTasks = ({ studioName }: IWidget) => {
  const currUser = useAppSelector(currentUser);

  console.info("currUser", currUser);
  const initialItems = [
    {
      label: "进行中",
      closable: false,
      key: "running",
      children: <TaskRunning studioName={studioName} />,
    },
    {
      label: "待领取",
      closable: false,
      key: "2",
      children: <TaskAssignee studioName={studioName} />,
    },
    {
      label: "已完成",
      key: "done",
      closable: false,
      children: <TaskDone studioName={studioName} />,
    },
  ];

  const [activeKey, setActiveKey] = useState(initialItems[0].key);
  const [items, setItems] = useState(initialItems);
  const newTabIndex = useRef(0);
  const onChange = (newActiveKey: string) => {
    setActiveKey(newActiveKey);
  };

  const add = () => {
    const newActiveKey = `newTab${newTabIndex.current++}`;
    const newPanes = [...items];
    newPanes.push({
      label: "New Tab",
      key: newActiveKey,
      closable: true,
      children: <TaskNew studioName={studioName} />,
    });
    setItems(newPanes);
    setActiveKey(newActiveKey);
  };

  const remove = (targetKey: TargetKey) => {
    let newActiveKey = activeKey;
    let lastIndex = -1;
    items.forEach((item, i) => {
      if (item.key === targetKey) {
        lastIndex = i - 1;
      }
    });
    const newPanes = items.filter((item) => item.key !== targetKey);
    if (newPanes.length && newActiveKey === targetKey) {
      if (lastIndex >= 0) {
        newActiveKey = newPanes[lastIndex].key;
      } else {
        newActiveKey = newPanes[0].key;
      }
    }
    setItems(newPanes);
    setActiveKey(newActiveKey);
  };

  const onEdit = (
    targetKey: React.MouseEvent | React.KeyboardEvent | string,
    action: "add" | "remove"
  ) => {
    if (action === "add") {
      add();
    } else {
      remove(targetKey);
    }
  };
  return (
    <Tabs
      type="editable-card"
      onChange={onChange}
      activeKey={activeKey}
      onEdit={onEdit}
      items={items}
    />
  );
};

export default MyTasks;
