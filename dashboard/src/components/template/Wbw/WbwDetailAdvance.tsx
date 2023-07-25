import { Input } from "antd";

import { IWbw } from "./WbwWord";

interface IWidget {
  data: IWbw;
  onChange?: Function;
}
const WbwDetailAdvanceWidget = ({ data, onChange }: IWidget) => {
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
        defaultValue={data.real?.value ? data.real?.value : ""}
        onChange={onRealChange}
      />
    </>
  );
};

export default WbwDetailAdvanceWidget;
