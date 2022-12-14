import { useIntl } from "react-intl";
import { Typography, Button } from "antd";
import { SwapOutlined } from "@ant-design/icons";
import type { MenuProps } from "antd";
import { Dropdown } from "antd";

import { IWbw, TWbwDisplayMode } from "./WbwWord";
import { PaliReal } from "../../../utils";
import "./wbw.css";

const { Text } = Typography;

const items: MenuProps["items"] = [
  {
    key: "n+m+sg+nom",
    label: "n+m+sg+nom",
  },
  {
    key: "un",
    label: "un",
  },
];

interface IWidget {
  data: IWbw;
  display?: TWbwDisplayMode;
  onSplit?: Function;
  onChange?: Function;
}
const Widget = ({ data, display, onSplit, onChange }: IWidget) => {
  const intl = useIntl();
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
