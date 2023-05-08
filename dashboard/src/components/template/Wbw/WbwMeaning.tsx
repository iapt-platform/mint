import { useState } from "react";
import { useIntl } from "react-intl";
import { Popover, Typography } from "antd";

import { PaliReal } from "../../../utils";
import { IWbw, TWbwDisplayMode } from "./WbwWord";
import WbwMeaningSelect from "./WbwMeaningSelect";
import { ArticleMode } from "../../article/Article";
import CaseFormula from "./CaseFormula";

const { Text } = Typography;

interface IWidget {
  data: IWbw;
  display?: TWbwDisplayMode;
  mode?: ArticleMode;
  onChange?: Function;
}
const Widget = ({
  data,
  display = "block",
  mode = "edit",
  onChange,
}: IWidget) => {
  const intl = useIntl();
  const [open, setOpen] = useState(false);
  let meaning = <></>;
  if (
    mode === "wbw" &&
    (typeof data.meaning === "undefined" ||
      data.meaning.value === null ||
      data.meaning.value.length === 0 ||
      data.meaning.value === "")
  ) {
    //空白的意思在逐词解析模式显示占位字符串
    meaning = (
      <Text type="secondary">
        {intl.formatMessage({ id: "dict.fields.meaning.label" })}
      </Text>
    );
  } else {
    meaning = <Text>{data.meaning?.value}</Text>;
  }
  const hide = () => {
    setOpen(false);
  };

  const handleOpenChange = (newOpen: boolean) => {
    setOpen(newOpen);
  };
  if (typeof data.real !== "undefined" && PaliReal(data.real.value) !== "") {
    //非标点符号
    return (
      <div style={{ display: "flex", justifyContent: "space-between" }}>
        <Popover
          open={open}
          onOpenChange={handleOpenChange}
          content={
            <div style={{ width: 500, height: "60vh", overflow: "auto" }}>
              <WbwMeaningSelect
                data={data}
                onSelect={(e: string) => {
                  hide();
                  if (typeof onChange !== "undefined") {
                    onChange(e);
                  }
                }}
              />
            </div>
          }
          placement="bottomLeft"
          trigger="hover"
        >
          {meaning}
        </Popover>
        <CaseFormula data={data} />
      </div>
    );
  } else {
    //标点符号
    return <></>;
  }
};

export default Widget;
