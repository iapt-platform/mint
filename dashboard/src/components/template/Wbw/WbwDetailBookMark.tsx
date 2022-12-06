import { useState } from "react";
import type { RadioChangeEvent } from "antd";
import { Radio } from "antd";
import { Input } from "antd";

import { IWbw } from "./WbwWord";

const { TextArea } = Input;

interface IWidget {
  data: IWbw;
  onChange?: Function;
}
const Widget = ({ data, onChange }: IWidget) => {
  const [value, setValue] = useState("none");

  const styleColor: React.CSSProperties = {
    display: "inline-block",
    width: 28,
    height: 18,
  };
  const options = [
    {
      label: (
        <span
          style={{
            ...styleColor,
            backgroundColor: "white",
          }}
        >
          none
        </span>
      ),
      value: "unset",
    },
    {
      label: (
        <span
          style={{
            ...styleColor,
            backgroundColor: "blue",
          }}
        ></span>
      ),
      value: "blue",
    },
    {
      label: (
        <span
          style={{
            ...styleColor,
            backgroundColor: "yellow",
          }}
        ></span>
      ),
      value: "yellow",
    },
  ];
  const onColorChange = ({ target: { value } }: RadioChangeEvent) => {
    console.log("radio3 checked", value);
    setValue(value);
    if (typeof onChange !== "undefined") {
      onChange({ field: "bookMarkColor", value: value });
    }
  };
  const onTextChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ) => {
    console.log("Change:", e.target.value);
    if (typeof onChange !== "undefined") {
      onChange({ field: "bookMarkText", value: e.target.value });
    }
  };
  return (
    <>
      <Radio.Group
        options={options}
        defaultValue={data.bookMarkColor?.value}
        onChange={onColorChange}
        value={value}
      />
      <TextArea
        defaultValue={data.bookMarkText?.value}
        showCount
        maxLength={512}
        autoSize={{ minRows: 6, maxRows: 8 }}
        onChange={onTextChange}
      />
    </>
  );
};

export default Widget;
