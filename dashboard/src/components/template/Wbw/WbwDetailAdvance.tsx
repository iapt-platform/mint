import { useState, useEffect } from "react";
import { useIntl } from "react-intl";
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
  const intl = useIntl();

  const onWordChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ) => {
    console.log("onWordChange:", e.target.value);
    if (typeof onChange !== "undefined") {
      onChange({ field: "word", value: e.target.value });
    }
  };
  const onRealChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ) => {
    console.log("onRealChange:", e.target.value);
    if (typeof onChange !== "undefined") {
      onChange({ field: "real", value: e.target.value });
    }
  };
  return (
    <>
      <div>显示</div>
      <Input
        showCount
        maxLength={512}
        defaultValue={data.word.value}
        onChange={onWordChange}
      />
      <div>拼写</div>
      <Input
        showCount
        maxLength={512}
        defaultValue={data.real?.value}
        onChange={onRealChange}
      />
    </>
  );
};

export default Widget;
