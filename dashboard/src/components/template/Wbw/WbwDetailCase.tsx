import { Button, Dropdown } from "antd";

import { MoreOutlined } from "@ant-design/icons";

import { useAppSelector } from "../../../hooks";
import { inlineDict as _inlineDict } from "../../../reducers/inline-dict";

import { IWbw } from "./WbwWord";

import SelectCase from "../../dict/SelectCase";
import { caseInDict } from "./WbwCase";
import { useIntl } from "react-intl";

interface IWidget {
  data: IWbw;
  onChange?: Function;
}
const WbwDetailCaseWidget = ({ data, onChange }: IWidget) => {
  const inlineDict = useAppSelector(_inlineDict);
  const intl = useIntl();

  return (
    <div style={{ display: "flex" }}>
      <SelectCase
        value={data.case?.value}
        onCaseChange={(value: string) => {
          if (typeof onChange !== "undefined") {
            onChange(value);
          }
        }}
      />
      <Dropdown
        menu={{
          items: data.real.value
            ? caseInDict(
                data.real.value,
                inlineDict.wordIndex,
                inlineDict.wordList,
                intl
              )
            : [],
          onClick: (e) => {
            console.log("click ", e.key);
            if (typeof onChange !== "undefined") {
              onChange(e.key);
            }
          },
        }}
        placement="bottomRight"
      >
        <Button type="text" icon={<MoreOutlined />} />
      </Dropdown>
    </div>
  );
};

export default WbwDetailCaseWidget;
