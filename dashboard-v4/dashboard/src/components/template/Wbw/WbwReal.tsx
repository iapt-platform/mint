import { Tooltip } from "antd";
import { Typography } from "antd";
import Lookup from "../../dict/Lookup";

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
    let wordReal: React.ReactNode = <></>;

    if (display === "block") {
      //block 模式下 限制宽度
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
      wordReal = (
        <>
          {data.real.value.split(" ").map((item, index) => (
            <Lookup search={item} key={index}>
              <Text type="secondary">{item} </Text>
            </Lookup>
          ))}
        </>
      );
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
