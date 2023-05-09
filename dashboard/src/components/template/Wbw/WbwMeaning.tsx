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
const WbwMeaningWidget = ({
  data,
  display = "block",
  mode = "edit",
  onChange,
}: IWidget) => {
  const intl = useIntl();
  const [open, setOpen] = useState(false);
  let meaning = <></>;
  if (
    typeof data.meaning?.value === "string" &&
    data.meaning.value.trim().length > 0
  ) {
    const eMeaning = data.meaning.value
      .replaceAll("[", "@[")
      .replaceAll("]", "]@")
      .replaceAll("{", "@{")
      .replaceAll("}", "}@")
      .split("@")
      .map((item, index) => {
        if (item.includes("[")) {
          return (
            <span key={index} style={{ color: "rosybrown" }}>
              {item.replaceAll("[", "").replaceAll("]", "")}
            </span>
          );
        } else if (item.includes("{")) {
          return (
            <span key={index} style={{ color: "lightskyblue" }}>
              {item.replaceAll("{", "").replaceAll("}", "")}
            </span>
          );
        } else {
          return <Text>{item}</Text>;
        }
      });
    meaning = <Text>{eMeaning}</Text>;
  } else if (mode === "wbw") {
    //空白的意思在逐词解析模式显示占位字符串
    meaning = (
      <Text type="secondary">
        {intl.formatMessage({ id: "dict.fields.meaning.label" })}
      </Text>
    );
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
            <div style={{ width: 500, height: "450px", overflow: "auto" }}>
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
        {mode === "wbw" ? (
          <CaseFormula
            data={data}
            onChange={(formula: string) => {
              /**
               * 有 [ ] 不替换
               * 有{ } 祛除 { }
               * 把 格位公式中的 ~ 替换为 data.meaning.value
               */
              if (
                data.meaning?.value &&
                data.meaning?.value.indexOf("[") >= 0
              ) {
                return;
              }
              let meaning: string = data.meaning?.value
                ? data.meaning?.value
                : "";
              meaning = meaning.replace(/\{(.+?)\}/g, "");

              meaning = formula
                .replaceAll("{", "[")
                .replaceAll("}", "]")
                .replace("~", meaning);
              if (typeof onChange !== "undefined") {
                onChange(meaning);
              }
            }}
          />
        ) : undefined}
      </div>
    );
  } else {
    //标点符号
    return <></>;
  }
};

export default WbwMeaningWidget;
