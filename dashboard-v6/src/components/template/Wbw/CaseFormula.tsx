import { MoreOutlined } from "@ant-design/icons";
import { Button, Dropdown, MenuProps } from "antd";
import { useEffect, useState } from "react";
import { useAppSelector } from "../../../hooks";
import { IWbw } from "./WbwWord";
import { inlineDict as _inlineDict } from "../../../reducers/inline-dict";

interface IWidget {
  data?: IWbw;
  onChange?: Function;
  onCaseChange?: Function;
}
const CaseFormulaWidget = ({ data, onChange, onCaseChange }: IWidget) => {
  const [formula, setFormula] = useState<MenuProps["items"]>([]);
  const inlineDict = useAppSelector(_inlineDict);

  useEffect(() => {
    if (
      typeof data === "undefined" ||
      typeof data.case === "undefined" ||
      typeof data.case.value !== "string"
    ) {
      setFormula([]);
      return;
    }
    const _case = data.case?.value?.split("#");
    if (_case?.length !== 2) {
      setFormula([]);
      return;
    }
    let grammar = _case[1];
    if (typeof grammar !== "string" || grammar.length === 0) {
      setFormula([]);
      return;
    }
    let result = inlineDict.wordList.filter(
      (word) => word.word === "_formula_" && word.grammar === grammar
    );
    if (result.length === 0) {
      //没找到，再次查找
      grammar = "*" + grammar.split("$").slice(1).join("$");
      result = inlineDict.wordList.filter(
        (word) => word.word === "_formula_" && word.grammar === grammar
      );
    }
    let strFormula: string;
    if (result.length > 0 && result[0].mean) {
      strFormula = result[0].mean;
    } else {
      strFormula = "{无}";
    }

    const menu1 = strFormula.split("/").map((item) => item.split("$"));
    const items = menu1[0].map((item1) => {
      const children = menu1[1]
        ? menu1[1].map((item2) => {
            let key: string;
            let label: string;
            if (item1.includes("@")) {
              key = item1.replace("@", item2);
              label = key;
            } else if (item2.includes("@")) {
              key = item2.replace("@", item1);
              label = key;
            } else {
              key = item1 + item2;
              label = item2;
            }
            return {
              key: key,
              label: label.replaceAll("{", "").replaceAll("}", ""),
            };
          })
        : undefined;
      return {
        key: item1,
        label: item1.replace("@", "~").replaceAll("{", "").replaceAll("}", ""),
        children: children,
      };
    });
    setFormula(items);
  }, [data?.case, inlineDict.wordList]);

  return (
    <Dropdown
      menu={{
        items: formula,
        onClick: (e) => {
          console.log("click ", e.key);
          if (typeof onChange !== "undefined") {
            onChange(e.key);
          }
        },
      }}
      placement="bottomRight"
    >
      <Button
        type="text"
        size="small"
        icon={<MoreOutlined />}
        onClick={(e) => e.preventDefault()}
      />
    </Dropdown>
  );
};

export default CaseFormulaWidget;
