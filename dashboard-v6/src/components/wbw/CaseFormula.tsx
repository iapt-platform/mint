import { MoreOutlined } from "@ant-design/icons";
import { Button, Dropdown, type MenuProps } from "antd";
import { useMemo } from "react";
import { useAppSelector } from "../../hooks";
import type { IWbw } from "../../types/wbw";
import { inlineDict as _inlineDict } from "../../reducers/inline-dict";

interface IWidget {
  data?: IWbw;
  onChange?: (key: string) => void;
}

const CaseFormulaWidget = ({ data, onChange }: IWidget) => {
  const inlineDict = useAppSelector(_inlineDict);

  const formula = useMemo<MenuProps["items"]>(() => {
    if (!data?.case?.value) return [];

    const _case = data.case.value.split("#");
    if (_case.length !== 2) return [];

    let grammar = _case[1];
    if (!grammar) return [];

    let result = inlineDict.wordList.filter(
      (word) => word.word === "_formula_" && word.grammar === grammar
    );

    if (result.length === 0) {
      grammar = "*" + grammar.split("$").slice(1).join("$");
      result = inlineDict.wordList.filter(
        (word) => word.word === "_formula_" && word.grammar === grammar
      );
    }

    const strFormula =
      result.length > 0 && result[0].mean ? result[0].mean : "{无}";

    const menu1 = strFormula.split("/").map((item) => item.split("$"));

    return menu1[0].map((item1) => {
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
              key,
              label: label.replaceAll("{", "").replaceAll("}", ""),
            };
          })
        : undefined;

      return {
        key: item1,
        label: item1.replace("@", "~").replaceAll("{", "").replaceAll("}", ""),
        children,
      };
    });
  }, [data, inlineDict.wordList]);

  return (
    <Dropdown
      menu={{
        items: formula,
        onClick: (e) => {
          onChange?.(e.key);
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
