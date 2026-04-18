import { useIntl } from "react-intl";
import { Cascader } from "antd";
import { useMemo, useState } from "react";
import { buildCaseOptions } from "./caseOptions";

interface IWidget {
  value?: string | null;
  readonly?: boolean;
  onCaseChange?: (value: string) => void;
}

const SelectCaseWidget = ({
  value,
  readonly = false,
  onCaseChange,
}: IWidget) => {
  const intl = useIntl();

  const options = useMemo(() => buildCaseOptions(intl), [intl]);

  // 直接从 value prop 派生，无需 useEffect + useState
  const currValue = useMemo(() => {
    if (typeof value !== "string") return undefined;
    return value
      .replaceAll("#", "$")
      .replaceAll(":", ".$.")
      .split("$")
      .map((item) => item.replaceAll(".", ""));
  }, [value]);

  const [internalValue, setInternalValue] = useState<
    (string | number)[] | undefined
  >(currValue);

  return (
    <Cascader
      disabled={readonly}
      value={internalValue ?? currValue}
      options={options}
      placeholder="Please select case"
      onChange={(value?: (string | number)[]) => {
        console.log("case changed", value);
        if (typeof value === "undefined") {
          setInternalValue(undefined);
          onCaseChange?.("");
          return;
        }

        let newValue: (string | number)[];
        if (
          value.length > 1 &&
          value[value.length - 1] === value[value.length - 2]
        ) {
          newValue = value.slice(0, -1);
        } else {
          newValue = value;
        }

        setInternalValue(newValue);

        if (typeof onCaseChange !== "undefined") {
          let output = newValue.map((item) => `.${item}.`).join("$");
          output = output.replace(".$.base", ":base").replace(".$.ind", ":ind");
          if (output.indexOf("$") > 0) {
            output =
              output.substring(0, output.indexOf("$")) +
              "#" +
              output.substring(output.indexOf("$") + 1);
          } else {
            output += "#";
          }
          onCaseChange(output);
        }
      }}
    />
  );
};

export default SelectCaseWidget;
