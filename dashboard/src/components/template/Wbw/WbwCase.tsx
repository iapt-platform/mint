import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import { Typography, Button, Space } from "antd";
import { SwapOutlined } from "@ant-design/icons";
import type { MenuProps } from "antd";
import { Dropdown } from "antd";
import { LoadingOutlined } from "@ant-design/icons";

import { IWbw, TWbwDisplayMode } from "./WbwWord";
import { PaliReal } from "../../../utils";
import "./wbw.css";
import { useAppSelector } from "../../../hooks";
import { inlineDict as _inlineDict } from "../../../reducers/inline-dict";

const { Text } = Typography;

interface IWidget {
  data: IWbw;
  display?: TWbwDisplayMode;
  onSplit?: Function;
  onChange?: Function;
}
const Widget = ({ data, display, onSplit, onChange }: IWidget) => {
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
        myMap.set(iterator.type + "$" + iterator.grammar, 1);
      }
      myMap.forEach((value, key, map) => {
        factors.push(key);
      });

      const menu = factors.map((item) => {
        const arrItem: string[] = item.replaceAll(".", "").split("$");
        let noNull = arrItem.filter((item) => item !== "");
        const key = noNull.join("$");
        noNull.forEach((item, index, arr) => {
          arr[index] = intl.formatMessage({
            id: `dict.fields.type.${item}.short.label`,
          });
        });
        return { key: key, label: noNull.join(" ") };
      });
      setItems(menu);
    }
  }, [data.word.value, inlineDict, intl]);
  const onClick: MenuProps["onClick"] = (e) => {
    console.log("click ", e);
    if (typeof onChange !== "undefined") {
      onChange(e.key);
    }
  };

  const showSplit: boolean = data.factors?.value.includes("+") ? true : false;
  let caseElement: JSX.Element | JSX.Element[] | undefined;
  if (
    display === "block" &&
    (typeof data.case === "undefined" ||
      data.case.value.length === 0 ||
      data.case.value[0] === "")
  ) {
    //空白的语法信息在逐词解析模式显示占位字符串
    caseElement = (
      <span>{intl.formatMessage({ id: "dict.fields.case.label" })}</span>
    );
  } else {
    caseElement = data.case?.value.map((item, id) => {
      if (item !== "") {
        return (
          <span key={id} className="case">
            {intl.formatMessage({
              id: `dict.fields.type.${item}.short.label`,
            })}
          </span>
        );
      } else {
        return <></>;
      }
    });
  }

  if (typeof data.real !== "undefined" && PaliReal(data.real.value) !== "") {
    return (
      <div className="wbw_word_item" style={{ display: "flex" }}>
        <Text type="secondary">
          <div>
            <Dropdown menu={{ items, onClick }} placement="bottomLeft">
              <span>{caseElement}</span>
            </Dropdown>

            {showSplit ? (
              <Button
                className="wbw_split"
                size="small"
                shape="circle"
                icon={<SwapOutlined />}
                onClick={() => {
                  if (typeof onSplit !== "undefined") {
                    onSplit(true);
                  }
                }}
              />
            ) : (
              <></>
            )}
          </div>
        </Text>
      </div>
    );
  } else {
    //标点符号
    return <></>;
  }
};

export default Widget;
