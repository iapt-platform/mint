import { Button, Input, Space, Tooltip } from "antd";
import { useState } from "react";
import { PlusOutlined, EditOutlined, CheckOutlined } from "@ant-design/icons";

import { MergeIcon } from "../../assets/icon";
import WbwFactorMeaningItem from "./WbwFactorMeaningItem";

const resizeArray = (input: string[], factors: string[]) => {
  const newFm = factors.map((_item, index) => {
    if (index < input.length) {
      return input[index];
    } else {
      return "";
    }
  });
  return newFm;
};
interface IWidget {
  factors?: string[];
  value?: string[];
  readonly?: boolean;
  onChange?: (data: string[]) => void;
  onJoin?: (newMeaning: string) => void;
}
const WbwDetailFmWidget = ({
  factors = [],
  value = [],
  readonly = false,
  onChange,
  onJoin,
}: IWidget) => {
  console.debug("WbwDetailFmWidget render");
  const [factorInputEnable, setFactorInputEnable] = useState(false);

  const currValue = resizeArray(value, factors);

  const combine = (input: string): string => {
    let meaning = "";
    input.split("-").forEach((value: string, index: number) => {
      if (index === 0) {
        meaning += value;
      } else {
        if (value.includes("~")) {
          meaning = value.replace("~", meaning);
        } else {
          meaning += value;
        }
      }
    });
    console.debug("combine", meaning);
    return meaning;
  };
  return (
    <div className="wbw_word_item" style={{ width: "100%" }}>
      <div style={{ display: "flex", width: "100%" }}>
        <Input
          key="input"
          allowClear
          hidden={!factorInputEnable}
          value={currValue.join("+")}
          placeholder="请输入"
          onChange={(e) => {
            console.log(e.target.value);
            const newData = resizeArray(e.target.value.split("+"), factors);
            if (typeof onChange !== "undefined") {
              onChange(newData);
            }
          }}
        />
        {factorInputEnable ? (
          <Button
            key="input-button"
            type="text"
            icon={<CheckOutlined />}
            onClick={() => setFactorInputEnable(false)}
          />
        ) : undefined}
      </div>
      {!factorInputEnable ? (
        <Space size={0} key="space">
          {currValue.map((item, index) => {
            const fm = item.split("-");
            return (
              <span key={index} style={{ display: "flex" }}>
                {factors[index]?.split("-").map((item1, index1) => {
                  return (
                    <WbwFactorMeaningItem
                      readonly={readonly}
                      key={index1}
                      pali={item1}
                      meaning={fm[index1]}
                      onChange={(value?: string) => {
                        if (value) {
                          const newData = [...currValue];
                          const currFm = resizeArray(
                            currValue[index].split("-"),
                            factors[index].split("-")
                          );
                          currFm.forEach(
                            (
                              _value3: string,
                              index3: number,
                              array: string[]
                            ) => {
                              if (index3 === index1) {
                                array[index3] = value;
                              }
                            }
                          );
                          newData[index] = currFm.join("-");
                          if (typeof onChange !== "undefined") {
                            onChange(newData);
                          }
                        }
                      }}
                    />
                  );
                })}

                {index < currValue.length - 1 ? (
                  <PlusOutlined disabled={readonly} key={`icon-${index}`} />
                ) : (
                  <>
                    <Tooltip title="在文本框中编辑">
                      <Button
                        disabled={readonly}
                        key="EditOutlined"
                        size="small"
                        type="text"
                        icon={<EditOutlined />}
                        onClick={() => setFactorInputEnable(true)}
                      />
                    </Tooltip>
                    <Tooltip title="合并后替换含义">
                      <Button
                        disabled={readonly}
                        key="CheckOutlined"
                        size="small"
                        type="text"
                        icon={<MergeIcon />}
                        onClick={() => {
                          if (typeof onJoin !== "undefined") {
                            const newMeaning = currValue
                              .map((item) => {
                                return item
                                  .replaceAll("[[", "/*")
                                  .replaceAll("]]", "*/");
                              })
                              .filter((value) => !value.includes("["))
                              .map((item) => {
                                return item
                                  .replaceAll("/*", "[[")
                                  .replaceAll("*/", "]]");
                              })
                              .map((item) => {
                                return combine(item);
                              })
                              .join("");
                            onJoin(newMeaning);
                          }
                        }}
                      />
                    </Tooltip>
                  </>
                )}
              </span>
            );
          })}
        </Space>
      ) : undefined}
    </div>
  );
};

export default WbwDetailFmWidget;
