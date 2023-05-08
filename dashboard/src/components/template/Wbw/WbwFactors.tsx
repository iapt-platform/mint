import { useState, useEffect } from "react";
import { useIntl } from "react-intl";
import type { MenuProps } from "antd";
import { Dropdown, Space, Typography } from "antd";
import { LoadingOutlined } from "@ant-design/icons";

import { IWbw, TWbwDisplayMode } from "./WbwWord";
import { PaliReal } from "../../../utils";
import { useAppSelector } from "../../../hooks";
import { inlineDict as _inlineDict } from "../../../reducers/inline-dict";
import { IApiResponseDictData } from "../../api/Dict";

const { Text } = Typography;

export const getFactorsInDict = (
  wordIn: string,
  wordIndex: string[],
  wordList: IApiResponseDictData[]
): string[] => {
  if (wordIndex.includes(wordIn)) {
    const result = wordList.filter((word) => word.word === wordIn);
    //查重
    //TODO 加入信心指数并排序
    let myMap = new Map<string, number>();
    let factors: string[] = [];
    for (const iterator of result) {
      myMap.set(iterator.factors, 1);
    }
    myMap.forEach((value, key, map) => {
      factors.push(key);
    });
    return factors;
  } else {
    return [];
  }
};

interface IWidget {
  data: IWbw;
  display?: TWbwDisplayMode;
  onChange?: Function;
}

const Widget = ({ data, display, onChange }: IWidget) => {
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
    if (inlineDict.wordIndex.includes(data.word.value)) {
      const result = inlineDict.wordList.filter(
        (word) => word.word === data.word.value
      );
      //查重
      //TODO 加入信心指数并排序
      let myMap = new Map<string, number>();
      let factors: string[] = [];
      for (const iterator of result) {
        myMap.set(iterator.factors, 1);
      }
      myMap.forEach((value, key, map) => {
        factors.push(key);
      });

      const menu = factors.map((item) => {
        return { key: item, label: item };
      });
      setItems(menu);
    }
  }, [data.word.value, inlineDict]);

  const onClick: MenuProps["onClick"] = (e) => {
    console.log("click ", e);
    if (typeof onChange !== "undefined") {
      onChange(e.key);
    }
  };

  let factors = <></>;
  if (
    display === "block" &&
    (typeof data.factors === "undefined" || data.factors.value === "")
  ) {
    //空白的意思在逐词解析模式显示占位字符串
    factors = (
      <Text type="secondary">
        {intl.formatMessage({ id: "dict.fields.factors.label" })}
      </Text>
    );
  } else {
    factors = <span>{data.factors?.value}</span>;
  }

  if (typeof data.real !== "undefined" && PaliReal(data.real.value) !== "") {
    return (
      <div>
        <Text type="secondary">
          <Dropdown menu={{ items, onClick }} placement="bottomLeft">
            {factors}
          </Dropdown>
        </Text>
      </div>
    );
  } else {
    //标点符号
    return <></>;
  }
};

export default Widget;
