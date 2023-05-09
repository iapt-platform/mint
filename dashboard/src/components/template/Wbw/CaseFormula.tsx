import { MoreOutlined } from "@ant-design/icons";
import { Button, Dropdown, MenuProps } from "antd";
import { useEffect, useState } from "react";
import { IWbw } from "./WbwWord";

interface IWidget {
  data?: IWbw;
  onChange?: Function;
}
const CaseFormulaWidget = ({ data, onChange }: IWidget) => {
  const [formula, setFormula] = useState<MenuProps["items"]>();

  useEffect(() => {
    const test3: string = "{其等},{他们},{她们},{它们}/{曾}~,~{完了},~{过}";
    const menu1 = test3.split("/").map((item) => item.split(","));
    const items = menu1[0].map((item1) => {
      const children = menu1[1]
        ? menu1[1].map((item2) => {
            return {
              key: item1 + item2,
              label: item2,
            };
          })
        : undefined;
      return {
        key: item1,
        label: item1,
        children: children,
      };
    });
    setFormula(items);
  }, [data?.case]);

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
