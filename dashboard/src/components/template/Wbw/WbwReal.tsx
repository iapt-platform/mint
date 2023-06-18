import { Tooltip } from "antd";
import { Typography } from "antd";

import { IWbw, TWbwDisplayMode } from "./WbwWord";

const { Text } = Typography;

interface IWidget {
  data: IWbw;
  display?: TWbwDisplayMode;
  onChange?: Function;
}

const WbwFactorsWidget = ({ data, display, onChange }: IWidget) => {
  if (
    typeof data.real?.value === "string" &&
    data.real.value.trim().length > 0
  ) {
    let wordReal = <></>;
    if (display === "block" || display === "list") {
      if (
        typeof data.real?.value === "string" &&
        data.real.value.trim().length > 0
      ) {
        if (display === "block") {
          const shortString = data.real.value.slice(
            0,
            data.word.value.length * 1.3 + 3
          );
          if (shortString === data.real.value) {
            wordReal = <span>{shortString}</span>;
          } else {
            wordReal = (
              <Tooltip title={data.real.value}>{`${shortString}…`}</Tooltip>
            );
          }
        } else {
          wordReal = <span>{data.real.value}</span>;
        }
      } else {
        //空白的意思在逐词解析模式显示占位字符串
        wordReal = <Text type="secondary">real</Text>;
      }
    }
    return (
      <div className="wbw_word_item">
        <Text type="secondary">{wordReal}</Text>
      </div>
    );
  } else {
    //标点符号
    return <></>;
  }
};

export default WbwFactorsWidget;
