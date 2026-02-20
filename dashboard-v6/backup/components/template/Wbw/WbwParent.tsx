import { useState, useEffect } from "react";
import { useIntl } from "react-intl";
import { type MenuProps, Tooltip } from "antd";
import { Dropdown, Space, Typography } from "antd";
import { LoadingOutlined } from "@ant-design/icons";

import type { IWbw, TWbwDisplayMode } from "./WbwWord";
import { useAppSelector } from "../../../hooks";
import { inlineDict as _inlineDict } from "../../../reducers/inline-dict";
import type { IApiResponseDictData } from "../../../api/Dict";
import { errorClass } from "./WbwMeaning";

const { Text } = Typography;

export const getParentInDict = (
  wordIn: string,
  wordIndex: string[],
  wordList: IApiResponseDictData[]
): string[] => {
  if (wordIndex.includes(wordIn)) {
    const result = wordList.filter((word) => word.word === wordIn);
    //查重
    //TODO 加入信心指数并排序
    const myMap = new Map<string, number>();
    const parents: string[] = [];
    for (const iterator of result) {
      if (iterator.parent) {
        myMap.set(iterator.parent, 1);
      }
    }
    myMap.forEach((_value, key, _map) => {
      parents.push(key);
    });
    return parents;
  } else {
    return [];
  }
};

interface IWidget {
  data: IWbw;
  answer?: IWbw;
  display?: TWbwDisplayMode;
  onChange?: Function;
}

const WbwParent = ({ data, answer, display, onChange }: IWidget) => {
  const intl = useIntl();
  const defaultMenu: MenuProps["items"] = [
    {
      key: "loading",
      label: (
        <Space>
          <LoadingOutlined />
          {"Loading"}
        </Space>
      ),
    },
  ];
  const [items, setItems] = useState<MenuProps["items"]>(defaultMenu);
  const inlineDict = useAppSelector(_inlineDict);

  useEffect(() => {
    if (!data.real.value) {
      return;
    }
    if (inlineDict.wordIndex.includes(data.real.value)) {
      const result = inlineDict.wordList.filter(
        (word) => word.word === data.real.value
      );
      //查重
      //TODO 加入信心指数并排序
      const myMap = new Map<string, number>();
      const parents: string[] = [];
      for (const iterator of result) {
        if (iterator.parent) {
          myMap.set(iterator.parent, 1);
        }
      }
      myMap.forEach((_value, key, _map) => {
        parents.push(key);
      });

      const menu = [...parents, data.real.value].map((item) => {
        return { key: item, label: item };
      });
      setItems(menu);
    }
  }, [data.real.value, inlineDict]);

  const onClick: MenuProps["onClick"] = (e) => {
    console.log("click ", e);
    if (typeof onChange !== "undefined") {
      onChange(e.key);
    }
  };

  if (
    typeof data.real?.value === "string" &&
    data.real.value.trim().length > 0
  ) {
    let parent = <></>;
    if (display === "block") {
      if (
        typeof data.parent?.value === "string" &&
        data.parent.value.trim().length > 0
      ) {
        const shortString = data.parent.value.slice(
          0,
          data.real.value.length + 6 + data.real.value.length / 3
        );
        if (shortString === data.parent.value) {
          parent = <span>{shortString}</span>;
        } else {
          parent = (
            <Tooltip title={data.parent.value}>{`${shortString}…`}</Tooltip>
          );
        }
      } else {
        //空白的意思在逐词解析模式显示占位字符串
        parent = (
          <Text type="secondary">
            {intl.formatMessage({ id: "forms.fields.parent.label" })}
          </Text>
        );
      }
    }
    const checkClass = answer
      ? errorClass("factors", data.factors?.value, answer?.factors?.value)
      : "";
    return (
      <div className={"wbw_word_item" + checkClass}>
        <Text type="secondary">
          <Dropdown menu={{ items, onClick }} placement="bottomLeft">
            {parent}
          </Dropdown>
        </Text>
      </div>
    );
  } else {
    //标点符号
    return <></>;
  }
};

export default WbwParent;
