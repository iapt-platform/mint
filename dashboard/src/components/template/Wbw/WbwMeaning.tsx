import { useState } from "react";
import { useIntl } from "react-intl";
import { Input, Popover, Typography } from "antd";

import { IWbw, TFieldName, TWbwDisplayMode } from "./WbwWord";
import WbwMeaningSelect from "./WbwMeaningSelect";
import { ArticleMode } from "../../article/Article";
import CaseFormula from "./CaseFormula";
import EditableLabel from "../../general/EditableLabel";

const { Text } = Typography;

export const errorClass = (
  field: TFieldName,
  data?: string | null,
  answer?: string | null
): string => {
  let classError = "";
  if (data && answer) {
    if (answer !== data) {
      classError = " wbw_check";
      switch (field) {
        case "parent":
          classError += " wbw_error";
          break;
        case "case":
          classError += " wbw_error";
          break;
        case "factors":
          classError += " wbw_warning";
          break;
        case "factorMeaning":
          classError += " wbw_info";
          break;
        case "meaning":
          classError += " wbw_info";
          break;
      }
    }
  }
  return classError;
};

interface IWidget {
  data: IWbw;
  answer?: IWbw;
  display?: TWbwDisplayMode;
  mode?: ArticleMode;
  onChange?: Function;
}
const WbwMeaningWidget = ({
  data,
  answer,
  display = "block",
  mode = "edit",
  onChange,
}: IWidget) => {
  const intl = useIntl();
  const [open, setOpen] = useState(false);
  const [input, setInput] = useState(data.meaning?.value);
  const [editable, setEditable] = useState(false);

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
          return <Text key={index}>{item}</Text>;
        }
      });
    meaning = <Text>{eMeaning}</Text>;
  } else if (mode === "wbw" || display === "inline") {
    //空白的意思在逐词解析模式显示占位字符串
    meaning = (
      <Text type="secondary">
        {intl.formatMessage({ id: "forms.fields.meaning.label" })}
      </Text>
    );
  }

  let meaningInner = <></>;
  if (display === "list") {
    meaningInner = (
      <EditableLabel
        defaultValue={data.meaning?.value ? data.meaning?.value : ""}
        value={data.meaning?.value ? data.meaning?.value : ""}
        placeholder="meaning"
        style={{ width: "100%" }}
        onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
          console.log("on change", event.target.value);
          setInput(event.target.value);
        }}
        onPressEnter={(event: React.KeyboardEvent<HTMLInputElement>) => {
          if (typeof onChange !== "undefined") {
            onChange(input ? input : "");
          }
        }}
        onKeyDown={(event: React.KeyboardEvent<HTMLInputElement>) => {}}
        onBlur={() => {
          if (typeof onChange !== "undefined") {
            onChange(input ? input : "");
          }
        }}
      />
    );
  } else if (editable) {
    meaningInner = (
      <Input
        defaultValue={data.meaning?.value ? data.meaning?.value : ""}
        placeholder="meaning"
        style={{ width: "100%" }}
        onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
          setInput(event.target.value);
        }}
        onPressEnter={(event: React.KeyboardEvent<HTMLInputElement>) => {
          if (typeof onChange !== "undefined") {
            onChange(input ? input : "");
          }
        }}
        onKeyDown={(event: React.KeyboardEvent<HTMLInputElement>) => {
          if (event.key === "Escape") {
            setEditable(false);
          }
        }}
        onBlur={(event) => {
          inputOk();
        }}
      />
    );
  } else {
    meaningInner = (
      <span
        onClick={(event: React.MouseEvent<HTMLSpanElement, MouseEvent>) => {
          setEditable(true);
        }}
      >
        {meaning}
      </span>
    );
  }

  const inputOk = () => {
    setEditable(false);
    if (typeof onChange !== "undefined") {
      onChange(input ? input : "");
    }
  };

  const hide = () => {
    setOpen(false);
  };

  const handleOpenChange = (newOpen: boolean) => {
    setOpen(newOpen);
  };
  if (
    typeof data.real?.value === "string" &&
    data.real.value.trim().length > 0
  ) {
    //非标点符号

    return (
      <div
        className={
          "wbw_word_item" +
          errorClass("meaning", data.meaning?.value, answer?.meaning?.value)
        }
      >
        {editable || display === "list" ? (
          meaningInner
        ) : (
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
              {meaningInner}
            </Popover>
            {mode === "wbw" ? (
              <CaseFormula
                data={data}
                onCaseChange={(formula: string) => {
                  if (
                    data.meaning?.value &&
                    data.meaning?.value.indexOf("[") >= 0
                  ) {
                    return;
                  }
                }}
                onChange={(formula: string) => {
                  /**
                   * 有 [ ] 不自动替换
                   * 有{ } 祛除 { }
                   * 把 格位公式中的 ~ 替换为 data.meaning.value
                   */
                  let meaning: string = data.meaning?.value
                    ? data.meaning?.value
                    : "";
                  meaning = meaning.replace(/\{(.+?)\}/g, "");
                  meaning = meaning.replace(/\[(.+?)\]/g, "");

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
        )}
      </div>
    );
  } else {
    //标点符号
    return <></>;
  }
};

export default WbwMeaningWidget;
