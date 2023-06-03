import { Button, Dropdown, Input, MenuProps, Space } from "antd";
import { useEffect, useState } from "react";
import {
  MoreOutlined,
  PlusOutlined,
  EditOutlined,
  CheckOutlined,
  SearchOutlined,
} from "@ant-design/icons";
import { useAppSelector } from "../../../hooks";

import { inlineDict as _inlineDict } from "../../../reducers/inline-dict";
import store from "../../../store";
import { lookup } from "../../../reducers/command";

interface IWFMI {
  pali: string;
  meaning: string;
  onChange?: Function;
}
const WbwFactorMeaningItem = ({ pali, meaning, onChange }: IWFMI) => {
  const defaultMenu: MenuProps["items"] = [
    {
      key: "_lookup",
      label: (
        <Space>
          <SearchOutlined />
          {"查字典"}
        </Space>
      ),
    },
    { key: pali, label: pali },
  ];
  const [items, setItems] = useState<MenuProps["items"]>(defaultMenu);

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
      console.log("menu", menu);
      setItems([...defaultMenu, ...menu]);
    }
  }, [pali, inlineDict]);
  return (
    <Dropdown
      menu={{
        items: items,
        onClick: (e) => {
          if (e.key === "_lookup") {
            store.dispatch(lookup(pali));
          } else if (typeof onChange !== "undefined") {
            onChange(e.key);
          }
        },
      }}
      placement="bottomLeft"
      trigger={["hover"]}
    >
      <Button
        key={1}
        size="small"
        type="text"
        icon={meaning === "" ? <MoreOutlined /> : undefined}
      >
        {meaning}
      </Button>
    </Dropdown>
  );
};

interface IWidget {
  factors?: string[];
  initValue?: string[];
  onChange?: Function;
  onJoin?: Function;
}
const WbwDetailFmWidget = ({
  factors = [],
  initValue = [],
  onChange,
  onJoin,
}: IWidget) => {
  const [factorInputEnable, setFactorInputEnable] = useState(false);
  const [factorMeaning, setFactorMeaning] = useState<string[]>(initValue);

  const resizeArray = (input: string[]) => {
    const newFm = factors.map((item, index) => {
      if (index < input.length) {
        return input[index];
      } else {
        return "";
      }
    });
    return newFm;
  };

  useEffect(() => {
    console.log("value", initValue);
    setFactorMeaning(resizeArray(initValue));
  }, []);

  useEffect(() => {
    console.log("factors", factors);
    if (factors.length === factorMeaning.length) {
      return;
    }
    setFactorMeaning(resizeArray(factorMeaning));
  }, [factors]);

  return (
    <div>
      <div style={{ display: "flex" }}>
        <Input
          key="input"
          allowClear
          hidden={!factorInputEnable}
          value={factorMeaning.join("+")}
          onChange={(e) => {
            console.log(e.target.value);
            const newData = resizeArray(e.target.value.split("+"));
            setFactorMeaning(newData);
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
          {factorMeaning.map((item, index) => {
            return (
              <span key={index}>
                <WbwFactorMeaningItem
                  key={index}
                  pali={factors[index]}
                  meaning={item}
                  onChange={(value: string) => {
                    const newData = [...factorMeaning];
                    newData[index] = value;
                    setFactorMeaning(newData);
                    if (typeof onChange !== "undefined") {
                      onChange(newData);
                    }
                  }}
                />

                {index < factorMeaning.length - 1 ? (
                  <PlusOutlined key={`icon-${index}`} />
                ) : (
                  <>
                    <Button
                      key="EditOutlined"
                      size="small"
                      type="text"
                      icon={<EditOutlined />}
                      onClick={() => setFactorInputEnable(true)}
                    />
                    <Button
                      key="CheckOutlined"
                      size="small"
                      type="text"
                      icon={<CheckOutlined />}
                      onClick={() => {
                        if (typeof onJoin !== "undefined") {
                          onJoin(
                            factorMeaning
                              .filter((value) => !value.includes("["))
                              .join(" ")
                          );
                        }
                      }}
                    />
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
