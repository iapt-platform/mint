import { useIntl } from "react-intl";
import { Button, Dropdown, Input, Space, Tooltip } from "antd";
import { useEffect, useState } from "react";
import {
  MoreOutlined,
  PlusOutlined,
  EditOutlined,
  CheckOutlined,
  SearchOutlined,
  CloseOutlined,
} from "@ant-design/icons";
import { useAppSelector } from "../../../hooks";

import { inlineDict as _inlineDict } from "../../../reducers/inline-dict";
import store from "../../../store";
import { lookup } from "../../../reducers/command";
import { openPanel } from "../../../reducers/right-panel";
import { ItemType } from "antd/lib/menu/hooks/useItems";
import { MergeIcon } from "../../../assets/icon";

interface IWFMI {
  pali: string;
  meaning?: string;
  readonly?: boolean;
  onChange?: Function;
}
const WbwFactorMeaningItem = ({
  pali,
  readonly = false,
  meaning = "",
  onChange,
}: IWFMI) => {
  const intl = useIntl();
  console.debug("WbwFactorMeaningItem meaning", meaning);
  const defaultMenu: ItemType[] = [
    {
      key: "_lookup",
      label: (
        <Space>
          <SearchOutlined />
          {intl.formatMessage({
            id: "buttons.lookup",
          })}
        </Space>
      ),
    },
    {
      key: "_edit",
      label: (
        <Space>
          <EditOutlined />
          {intl.formatMessage({
            id: "buttons.edit",
          })}
        </Space>
      ),
    },
    { key: pali, label: pali },
  ];
  const [items, setItems] = useState<ItemType[]>(defaultMenu);
  const [input, setInput] = useState<string>();
  const [editable, setEditable] = useState(false);

  const inlineDict = useAppSelector(_inlineDict);
  useEffect(() => {
    if (inlineDict.wordIndex.includes(pali)) {
      const result = inlineDict.wordList.filter((word) => word.word === pali);
      //查重
      //TODO 加入信心指数并排序
      let myMap = new Map<string, number>();
      let meanings: string[] = [];
      for (const it of result) {
        if (typeof it.mean === "string") {
          for (const meaning of it.mean.split("$")) {
            if (meaning !== "") {
              myMap.set(meaning, 1);
            }
          }
        }
      }
      myMap.forEach((value, key, map) => {
        meanings.push(key);
      });

      const menu = meanings.map((item) => {
        return { key: item, label: item };
      });
      setItems([...defaultMenu, ...menu]);
    }
  }, [pali, inlineDict]);

  const inputOk = () => {
    setEditable(false);
    if (typeof onChange !== "undefined") {
      onChange(input);
    }
  };

  const inputCancel = () => {
    setEditable(false);
    setInput(meaning);
  };

  const meaningInner = editable ? (
    <Input
      defaultValue={meaning}
      size="small"
      addonAfter={
        <>
          <CheckOutlined
            style={{ cursor: "pointer", marginRight: 4 }}
            onClick={() => inputOk()}
          />
          <CloseOutlined
            style={{ cursor: "pointer" }}
            onClick={() => inputCancel()}
          />
        </>
      }
      placeholder="Basic usage"
      style={{ width: 160 }}
      onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
        setInput(event.target.value);
      }}
      onPressEnter={(event: React.KeyboardEvent<HTMLInputElement>) => {
        inputOk();
      }}
      onKeyDown={(event: React.KeyboardEvent<HTMLInputElement>) => {
        if (event.key === "Escape") {
          setEditable(false);
        }
      }}
    />
  ) : (
    <Button
      disabled={readonly}
      key={1}
      size="small"
      type="text"
      icon={meaning === "" ? <MoreOutlined /> : undefined}
      onClick={() => {
        setEditable(true);
      }}
    >
      {meaning}
    </Button>
  );

  return editable || readonly ? (
    meaningInner
  ) : (
    <Dropdown
      menu={{
        items: [
          ...items.filter((value, index) => index <= 5),
          {
            key: "more",
            label: intl.formatMessage({ id: "buttons.more" }),
            disabled: items.length <= 5,
            children: items.filter((value, index) => index > 5),
          },
        ],
        onClick: (e) => {
          switch (e.key) {
            case "_lookup":
              store.dispatch(lookup(pali));
              store.dispatch(openPanel("dict"));
              break;
            case "_edit":
              setEditable(true);
              break;
            default:
              if (typeof onChange !== "undefined") {
                onChange(e.key);
              }
              break;
          }
        },
      }}
      placement="bottomLeft"
      trigger={["hover"]}
    >
      {meaningInner}
    </Dropdown>
  );
};
const resizeArray = (input: string[], factors: string[]) => {
  const newFm = factors.map((item, index) => {
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
  onChange?: Function;
  onJoin?: Function;
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
    input
      .split("-")
      .forEach((value: string, index: number, array: string[]) => {
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
                      onChange={(value: string) => {
                        const newData = [...currValue];
                        let currFm = resizeArray(
                          currValue[index].split("-"),
                          factors[index].split("-")
                        );
                        currFm.forEach(
                          (value3: string, index3: number, array: string[]) => {
                            if (index3 === index1) {
                              array[index3] = value;
                            }
                          }
                        );
                        newData[index] = currFm.join("-");
                        if (typeof onChange !== "undefined") {
                          onChange(newData);
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
