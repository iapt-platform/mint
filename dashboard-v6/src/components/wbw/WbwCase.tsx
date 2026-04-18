import { useMemo, type JSX } from "react";
import { useIntl } from "react-intl";
import { Typography, Button, Space } from "antd";
import { SwapOutlined } from "@ant-design/icons";
import type { MenuProps } from "antd";
import { Dropdown } from "antd";
import { LoadingOutlined } from "@ant-design/icons";

import "./wbw.css";
import { useAppSelector } from "../../hooks";
import { inlineDict as _inlineDict } from "../../reducers/inline-dict";
import WbwParent2 from "./WbwParent2";

import WbwParentIcon from "./WbwParentIcon";
import { caseInDict, errorClass } from "./utils";
import type { IWbw, TWbwDisplayMode } from "../../types/wbw";

export interface ValueType {
  key: string;
  label: string;
}

const { Text } = Typography;

interface IWidget {
  data: IWbw;
  answer?: IWbw;
  display?: TWbwDisplayMode;
  onSplit?: (v: boolean) => void;
  onChange?: (key: string) => void;
}
const WbwCaseWidget = ({
  data,
  answer,
  display,
  onSplit,
  onChange,
}: IWidget) => {
  const intl = useIntl();

  const inlineDict = useAppSelector(_inlineDict);

  const items = useMemo(() => {
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

    if (!data.real.value) return defaultMenu;

    return caseInDict(
      data.real.value,
      inlineDict.wordIndex,
      inlineDict.wordList,
      intl
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
