import { Divider, Input, InputNumber } from "antd";

import { ITaskData } from "../api/task";
import "../article/article.css";
import { useEffect, useState } from "react";
import ChannelSelectWithToken from "../channel/ChannelSelectWithToken";
import { TChannelType } from "../api/Channel";
import { TPower } from "../api/token";

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
const TaskBuilderProp = ({
  workflow,
  channelsId,
  book,
  para,
  onChange,
}: IWidget) => {
  //console.debug("TaskBuilderProp render");
  const [prop, setProp] = useState<IProp[]>();
  useEffect(() => {
    const newProp = workflow?.map((item) => {
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
          const [k, v] = item.split("=");
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
      if (num) {
        output = [...output, ...num];
      }
      if (constant) {
        output = [...output, ...constant];
      }
      return {
        taskTitle: item.title,
        taskId: item.id,
        param: output,
      };
    });
    setProp(newProp);
  }, [workflow]);

  const change = (
    tIndex: number,
    pIndex: number,
    value: string,
    initValue: number,
    step: number
  ) => {
    const newData = prop?.map((item, tId) => {
      return {
        taskTitle: item.taskTitle,
        taskId: item.taskId,
        param: item.param?.map((param, pId) => {
          if (tIndex === tId && pIndex === pId) {
            return { ...param, value: value, initValue: initValue, step: step };
          } else {
            return param;
          }
        }),
      };
    });
    setProp(newData);
    console.debug("newData", newData);
    onChange && onChange(newData);
  };

  const Value = (item: IParam, taskId: number, paramId: number) => {
    let channelType: string | undefined;
    const [key, channel, power] = item.key.replaceAll("%", "").split("@");
    if (item.key.includes("@channel")) {
      if (channel.includes(":")) {
        channelType = channel.split(":")[1].replaceAll("%", "");
      }
    }
    return item.type === "number" ? (
      <InputNumber
        defaultValue={item.initValue}
        value={item.initValue}
        onChange={(e) => {
          if (e) {
            change(taskId, paramId, item.value, e, item.step);
          }
        }}
      />
    ) : item.type === "string" ? (
      <Input
        defaultValue={item.value}
        value={item.value}
        onChange={(e) => {
          if (e) {
            change(taskId, paramId, e.target.value, item.initValue, item.step);
          }
        }}
      />
    ) : (
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
    return item.type === "string" ? (
      <>{"无"}</>
    ) : item.value === "?" ? (
      <>{"无"}</>
    ) : (
      <InputNumber
        defaultValue={item.step}
        readOnly={item.value === "?++"}
        onChange={(e) => {
          if (e) {
            change(taskId, paramId, item.value, item.initValue, e);
          }
        }}
      />
    );
  };
  return (
    <>
      {prop?.map((item, taskId) => {
        return (
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
                {item.param?.map((item, paramId) => {
                  return (
                    <tr key={paramId}>
                      <td key={1}>{item.label}</td>
                      <td key={2}>{Value(item, taskId, paramId)}</td>
                      <td key={3}>{Step(item, taskId, paramId)}</td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        );
      })}
    </>
  );
};

export default TaskBuilderProp;
