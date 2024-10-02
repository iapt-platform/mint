import { useState, useEffect } from "react";
import { useIntl } from "react-intl";
import { MenuProps, Tooltip } from "antd";
import { Dropdown, Space, Typography } from "antd";
import { LoadingOutlined } from "@ant-design/icons";

import { IWbw, TWbwDisplayMode } from "./WbwWord";
import { useAppSelector } from "../../../hooks";
import { inlineDict as _inlineDict } from "../../../reducers/inline-dict";
import { IApiResponseDictData } from "../../api/Dict";
import { errorClass } from "./WbwMeaning";

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
      if (iterator.factors) {
        myMap.set(iterator.factors, 1);
      }
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
  answer?: IWbw;
  display?: TWbwDisplayMode;
  onChange?: Function;
}

const WbwFactorsWidget = ({ data, answer, display, onChange }: IWidget) => {
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
      let myMap = new Map<string, number>();
      let factors: string[] = [];
      for (const iterator of result) {
        if (iterator.factors) {
          myMap.set(iterator.factors, 1);
        }
      }
      myMap.forEach((value, key, map) => {
        factors.push(key);
      });

      const menu = factors.map((item) => {
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
    let factors = <></>;
    if (display === "block") {
      if (
        typeof data.factors?.value === "string" &&
        data.factors.value.trim().length > 0
      ) {
        const shortString = data.factors.value.slice(
          0,
          data.real.value.length + 6 + data.real.value.length / 3
        );
        if (shortString === data.factors.value) {
          factors = <span>{shortString}</span>;
        } else {
          factors = (
            <Tooltip title={data.factors.value}>{`${shortString}…`}</Tooltip>
          );
        }
      } else {
        //空白的意思在逐词解析模式显示占位字符串
        factors = (
          <Text type="secondary">
            {intl.formatMessage({ id: "forms.fields.factors.label" })}
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

export default WbwFactorsWidget;
