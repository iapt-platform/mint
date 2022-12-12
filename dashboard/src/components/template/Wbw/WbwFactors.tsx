import { useIntl } from "react-intl";
import type { MenuProps } from "antd";
import { Dropdown } from "antd";
import { Typography } from "antd";

import { IWbw, TWbwDisplayMode } from "./WbwWord";
import { PaliReal } from "../../../utils";
const { Text } = Typography;

const items: MenuProps["items"] = [
  {
    key: "factor1+word",
    label: "factor1+word",
  },
  {
    key: "factor2+word",
    label: "factor2+word",
  },
  {
    key: "factor3+word",
    label: "factor3+word",
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
