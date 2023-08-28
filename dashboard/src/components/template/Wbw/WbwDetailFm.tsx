import { useIntl } from "react-intl";
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
import { openPanel } from "../../../reducers/right-panel";

interface IWFMI {
  pali: string;
  meaning?: string;
  onChange?: Function;
}
const WbwFactorMeaningItem = ({ pali, meaning, onChange }: IWFMI) => {
  const intl = useIntl();
  const defaultMenu: MenuProps["items"] = [
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
  const [items, setItems] = useState<MenuProps["items"]>(defaultMenu);
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

  const meaningInner = editable ? (
    <Input
      defaultValue={meaning}
      size="small"
      addonAfter={
        <CheckOutlined
          style={{ cursor: "pointer" }}
          onClick={() => inputOk()}
        />
      }
      placeholder="Basic usage"
      style={{ width: 100 }}
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

  return editable ? (
    meaningInner
  ) : (
    <Dropdown
      menu={{
        items: items,
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
              <span key={index} style={{ display: "flex" }}>
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
