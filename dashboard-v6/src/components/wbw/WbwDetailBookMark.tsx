import { useState } from "react";
import type { RadioChangeEvent } from "antd";
import { Radio } from "antd";
import { Input } from "antd";

import type { IWbw, IWbwField } from "../../types/wbw";
import { bookMarkColor } from "./utils";

const { TextArea } = Input;

interface IWidget {
  data: IWbw;
  onChange?: (value: IWbwField) => void;
}
const WbwDetailBookMarkWidget = ({ data, onChange }: IWidget) => {
  const [value, setValue] = useState("none");

  const styleColor: React.CSSProperties = {
    display: "inline-block",
    width: 28,
    height: 18,
  };

  const options = bookMarkColor.map((item, id) => {
    return {
      label: (
        <span
          style={{
            ...styleColor,
            backgroundColor: item,
          }}
        ></span>
      ),
      value: id,
    };
  });

  const onColorChange = ({ target: { value } }: RadioChangeEvent) => {
    console.log("radio3 checked", value);
    if (onChange) {
      onChange({ field: "bookMarkColor", value: value });
    } else {
      setValue(value);
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
        defaultValue={
          data.bookMarkText?.value ? data.bookMarkText?.value : undefined
        }
        showCount
        maxLength={512}
        autoSize={{ minRows: 6, maxRows: 8 }}
        onChange={onTextChange}
      />
    </>
  );
};

export default WbwDetailBookMarkWidget;
