import { useEffect, useState } from "react";
import { IntlShape, useIntl } from "react-intl";
import { Typography, Button, Space } from "antd";
import { SwapOutlined } from "@ant-design/icons";
import type { MenuProps } from "antd";
import { Dropdown } from "antd";
import { LoadingOutlined, FileExclamationOutlined } from "@ant-design/icons";

import { IWbw, TWbwDisplayMode } from "./WbwWord";
import "./wbw.css";
import { useAppSelector } from "../../../hooks";
import { inlineDict as _inlineDict } from "../../../reducers/inline-dict";
import WbwParent2 from "./WbwParent2";
import { IApiResponseDictData } from "../../api/Dict";
import { errorClass } from "./WbwMeaning";
import WbwParentIcon from "./WbwParentIcon";

export interface ValueType {
  key: string;
  label: string;
}

const { Text } = Typography;

export const caseInDict = (
  wordIn: string,
  wordIndex: string[],
  wordList: IApiResponseDictData[],
  intl: IntlShape
): MenuProps["items"] => {
  if (!wordIn) {
    return [];
  }
  if (wordIndex.includes(wordIn)) {
    const result = wordList.filter((word) => word.word === wordIn);
    //查重
    //TODO 加入信心指数并排序
    let myMap = new Map<string, number>();
    let factors: string[] = [];
    for (const iterator of result) {
      myMap.set(iterator.type + "#" + iterator.grammar, 1);
    }
    myMap.forEach((value, key, map) => {
      factors.push(key);
    });

    const menu = factors.map((item) => {
      const arrItem: string[] = item
        .replaceAll(".", "")
        .replaceAll("#", "$")
        .split("$");
      let noNull = arrItem.filter((item) => item !== "");
      noNull.forEach((item, index, arr) => {
        arr[index] = intl.formatMessage({
          id: `dict.fields.type.${item}.short.label`,
          defaultMessage: item,
        });
      });
      return { key: item, label: noNull.join(" ") };
    });
    if (menu.length > 0) {
      return menu;
    } else {
      return [
        {
          key: "",
          disabled: true,
          label: (
            <>
              <FileExclamationOutlined />{" "}
              {intl.formatMessage({
                id: "labels.empty",
              })}
            </>
          ),
        },
      ];
    }
  } else {
    return [
      {
        key: "",
        disabled: true,
        label: (
          <>
            <LoadingOutlined />{" "}
            {intl.formatMessage({
              id: "labels.loading",
            })}
          </>
        ),
      },
    ];
  }
};

interface IWidget {
  data: IWbw;
  answer?: IWbw;
  display?: TWbwDisplayMode;
  onSplit?: Function;
  onChange?: Function;
}
const WbwCaseWidget = ({
  data,
  answer,
  display,
  onSplit,
  onChange,
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
    if (!data.real.value) {
      return;
    }
    setItems(
      caseInDict(
        data.real.value,
        inlineDict.wordIndex,
        inlineDict.wordList,
        intl
      )
    );
  }, [data.real.value, inlineDict, intl]);
  const onClick: MenuProps["onClick"] = (e) => {
    console.log("click ", e);
    if (typeof onChange !== "undefined") {
      onChange(e.key);
    }
  };

  const showSplit: boolean = data.factors?.value?.includes("+") ? true : false;
  let caseElement: JSX.Element | JSX.Element[] | undefined;
  if (display === "block") {
    if (
      typeof data.case?.value === "string" &&
      data.case.value?.trim().length > 0
    ) {
      caseElement = data.case.value
        .replace("#", "$")
        .split("$")
        .map((item, id) => {
          if (item !== "") {
            const strCase = item.replaceAll(".", "");
            return (
              <span key={id} className="case">
                {intl.formatMessage({
                  id: `dict.fields.type.${strCase}.short.label`,
                  defaultMessage: strCase,
                })}
              </span>
            );
          } else {
            return <span key={id}>-</span>;
          }
        });
    } else {
      //空白的语法信息在逐词解析模式显示占位字符串
      caseElement = (
        <span>{intl.formatMessage({ id: "forms.fields.case.label" })}</span>
      );
    }
  }

  if (
    typeof data.real?.value === "string" &&
    data.real.value.trim().length > 0
  ) {
    //非标点符号
    const checkClass = answer
      ? errorClass("case", data.case?.value, answer?.case?.value)
      : "";
    return (
      <div className={"wbw_word_item"} style={{ display: "flex" }}>
        <Text type="secondary">
          <div>
            <span className={checkClass}>
              <Dropdown
                key="dropdown"
                menu={{ items, onClick }}
                placement="bottomLeft"
              >
                <span>{caseElement}</span>
              </Dropdown>
            </span>
            <WbwParentIcon data={data} answer={answer} />
            <WbwParent2 data={data} />
            {showSplit ? (
              <Button
                key="button"
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
            ) : undefined}
          </div>
        </Text>
      </div>
    );
  } else {
    //标点符号
    return <></>;
  }
};

export default WbwCaseWidget;
