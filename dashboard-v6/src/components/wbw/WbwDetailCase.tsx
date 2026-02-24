import { Button, Dropdown } from "antd";

import { MoreOutlined } from "@ant-design/icons";

import { useAppSelector } from "../../hooks";
import { inlineDict as _inlineDict } from "../../reducers/inline-dict";

import type { IWbw } from "../../types/wbw";

import { useIntl } from "react-intl";
import { caseInDict } from "./utils";
import SelectCase from "./SelectCase";

interface IWidget {
  data: IWbw;
  readonly?: boolean;
  onChange?: (value: string) => void;
}
const WbwDetailCaseWidget = ({ data, readonly = false, onChange }: IWidget) => {
  const inlineDict = useAppSelector(_inlineDict);
  const intl = useIntl();
  console.debug("readonly", readonly);
  return (
    <div style={{ display: "flex", width: "100%" }}>
      <SelectCase
        readonly={readonly}
        value={data.case?.value}
        onCaseChange={(value: string) => {
          if (typeof onChange !== "undefined") {
            onChange(value);
          }
        }}
      />
      <Dropdown
        trigger={readonly ? [] : ["click"]}
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
        <Button disabled={readonly} type="text" icon={<MoreOutlined />} />
      </Dropdown>
    </div>
  );
};

export default WbwDetailCaseWidget;
