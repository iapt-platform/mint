import { Divider, Input, InputNumber } from "antd";

import type { ITaskData } from "../../api/task";
import "../article/article.css";
import { useState } from "react";
import ChannelSelectWithToken from "../channel/ChannelSelectWithToken";

import type { TPower } from "../../api/token";
import type { TChannelType } from "../../api/channel";

type TParamType =
  | "number"
  | "string"
  | "channel:translation"
  | "channel:nissaya";

export interface IParam {
  key: string;
  label: string;
  value: string;
  type: TParamType;
  initValue: number;
  step: number;
}

export interface IProp {
  taskTitle: string;
  taskId: string;
  param?: IParam[];
}

interface IWidget {
  workflow?: ITaskData[];
  channelsId?: string[];
  book?: number;
  para?: number;
  onChange?: (data: IProp[] | undefined) => void;
}

const buildProp = (workflow: ITaskData[] | undefined): IProp[] | undefined => {
  return workflow?.map((item) => {
    const num = item.description
      ?.replaceAll("}}", "|}}")
      .split("|")
      .filter((value) => value.includes("=?"))
      .map((item) => {
        const [k, v] = item.split("=");
        const value: IParam = {
          key: k,
          label: k,
          value: v,
          type: "number",
          initValue: 1,
          step: v === "?++" ? 1 : v === "?+" ? 0 : -1,
        };
        return value;
      });

    const constant = item.description
      ?.replaceAll("}}", "|}}")
      .split("|")
      .filter((value) => value.includes("=%"))
      .map((item) => {
        const [, v] = item.split("=");
        const paramKey = v.split("@");
        const value: IParam = {
          key: v,
          label: paramKey[0],
          value: "",
          type:
            paramKey.length > 1 && paramKey[1]
              ? (paramKey[1] as TParamType)
              : "string",
          initValue: 0,
          step: 0,
        };
        return value;
      });

    let output: IParam[] = [];
    if (num) output = [...output, ...num];
    if (constant) output = [...output, ...constant];

    return {
      taskTitle: item.title,
      taskId: item.id,
      param: output,
    };
  });
};

const TaskBuilderProp = ({
  workflow,
  channelsId,
  book,
  para,
  onChange,
}: IWidget) => {
  // Store the previous workflow reference in state so we can compare during render
  // without touching refs. This is the React-recommended pattern for derived state
  // that must reset when a prop changes:
  // https://react.dev/learn/you-might-not-need-an-effect#adjusting-some-state-when-a-prop-changes
  const [prevWorkflow, setPrevWorkflow] = useState(workflow);
  const [prop, setProp] = useState(() => buildProp(workflow));

  if (prevWorkflow !== workflow) {
    // Runs synchronously during render — React will discard this render output
    // and immediately re-render with the updated state, so only one extra render occurs.
    setPrevWorkflow(workflow);
    setProp(buildProp(workflow));
  }

  const change = (
    tIndex: number,
    pIndex: number,
    value: string,
    initValue: number,
    step: number
  ) => {
    const newData = prop?.map((item, tId) => ({
      taskTitle: item.taskTitle,
      taskId: item.taskId,
      param: item.param?.map((param, pId) => {
        if (tIndex === tId && pIndex === pId) {
          return { ...param, value, initValue, step };
        }
        return param;
      }),
    }));
    setProp(newData);
    console.debug("newData", newData);
    onChange?.(newData);
  };

  const Value = (item: IParam, taskId: number, paramId: number) => {
    let channelType: string | undefined;
    const [, channel, power] = item.key.replaceAll("%", "").split("@");
    if (item.key.includes("@channel") && channel.includes(":")) {
      channelType = channel.split(":")[1].replaceAll("%", "");
    }

    if (item.type === "number") {
      return (
        <InputNumber
          value={item.initValue}
          onChange={(e) => {
            if (e) change(taskId, paramId, item.value, e, item.step);
          }}
        />
      );
    }

    if (item.type === "string") {
      return (
        <Input
          value={item.value}
          onChange={(e) => {
            change(taskId, paramId, e.target.value, item.initValue, item.step);
          }}
        />
      );
    }

    return (
      <ChannelSelectWithToken
        channelsId={channelsId}
        book={book}
        para={para}
        type={channelType as TChannelType}
        power={power ? (power as TPower) : undefined}
        onChange={(e) => {
          console.debug("channel select onChange", e);
          change(taskId, paramId, e ?? "", item.initValue, item.step);
        }}
      />
    );
  };

  const Step = (item: IParam, taskId: number, paramId: number) => {
    if (item.type === "string" || item.value === "?") {
      return <>{"无"}</>;
    }
    return (
      <InputNumber
        defaultValue={item.step}
        readOnly={item.value === "?++"}
        onChange={(e) => {
          if (e) change(taskId, paramId, item.value, item.initValue, e);
        }}
      />
    );
  };

  return (
    <>
      {prop?.map((item, taskId) => (
        <div key={taskId}>
          <Divider>{item.taskTitle}</Divider>
          <table>
            <thead>
              <tr>
                <td>变量名</td>
                <td>值</td>
                <td>递增步长</td>
              </tr>
            </thead>
            <tbody>
              {item.param?.map((param, paramId) => (
                <tr key={paramId}>
                  <td>{param.label}</td>
                  <td>{Value(param, taskId, paramId)}</td>
                  <td>{Step(param, taskId, paramId)}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      ))}
    </>
  );
};

export default TaskBuilderProp;
