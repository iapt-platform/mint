import { useIntl } from "react-intl";
import type { MenuProps } from "antd";
import { Dropdown } from "antd";
import { Typography } from "antd";

import { IWbw, TWbwDisplayMode } from "./WbwWord";
import { PaliReal } from "../../../utils";
const { Text } = Typography;

const items: MenuProps["items"] = [
  {
    key: "factor1+意思",
    label: "factor1+意思",
  },
  {
    key: "factor2+意思",
    label: "factor2+意思",
  },
  {
    key: "factor3+意思",
    label: "factor3+意思",
  },
];

interface IWidget {
  data: IWbw;
  display?: TWbwDisplayMode;
  onChange?: Function;
}
const Widget = ({ data, display, onChange }: IWidget) => {
  const intl = useIntl();

  const onClick: MenuProps["onClick"] = (e) => {
    console.log("click ", e);
    if (typeof onChange !== "undefined") {
      onChange(e.key);
    }
  };

  let factorMeaning = <></>;
  if (
    display === "block" &&
    (typeof data.factorMeaning === "undefined" ||
      data.factorMeaning.value === "")
  ) {
    //空白的意思在逐词解析模式显示占位字符串
    factorMeaning = (
      <Text type="secondary">
        {intl.formatMessage({ id: "dict.fields.factormeaning.label" })}
      </Text>
    );
  } else {
    factorMeaning = <span>{data.factorMeaning?.value}</span>;
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

export default Widget;
