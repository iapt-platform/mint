import { useIntl } from "react-intl";
import { useState, useEffect } from "react";
import type { MenuProps } from "antd";
import { Dropdown, Space, Typography } from "antd";
import { LoadingOutlined } from "@ant-design/icons";

import { IWbw, TWbwDisplayMode } from "./WbwWord";
import { PaliReal } from "../../../utils";
import { useAppSelector } from "../../../hooks";
import { inlineDict as _inlineDict } from "../../../reducers/inline-dict";

const { Text } = Typography;

interface IWidget {
  data: IWbw;
  factors?: string;
  display?: TWbwDisplayMode;
  onChange?: Function;
}
const WbwFactorMeaningWidget = ({
  data,
  display,
  onChange,
  factors,
}: IWidget) => {
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
        myMap.set(iterator.factormean, 1);
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

  useEffect(() => {
    if (typeof factors !== "undefined") {
    }
  }, [factors]);

  const onClick: MenuProps["onClick"] = (e) => {
    console.log("click ", e);
    if (typeof onChange !== "undefined") {
      onChange(e.key);
    }
  };

  let factorMeaning = <></>;
  if (display === "block") {
    if (
      typeof data.factorMeaning?.value === "string" &&
      data.factorMeaning.value.trim().length > 0
    ) {
      factorMeaning = <span>{data.factorMeaning?.value}</span>;
    } else {
      //空白的意思在逐词解析模式显示占位字符串
      factorMeaning = (
        <Text type="secondary">
          {intl.formatMessage({ id: "dict.fields.factormeaning.label" })}
        </Text>
      );
    }
  }

  if (typeof data.real !== "undefined" && PaliReal(data.real.value) !== "") {
    return (
      <div>
        <Text type="secondary">
          <Dropdown menu={{ items, onClick }} placement="bottomLeft">
            {factorMeaning}
          </Dropdown>
        </Text>
      </div>
    );
  } else {
    //标点符号
    return <></>;
  }
};

export default WbwFactorMeaningWidget;