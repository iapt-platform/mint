import { Input } from "antd";

import { IWbw } from "./WbwWord";

const { TextArea } = Input;

interface IWidget {
  data: IWbw;
  onChange?: Function;
}
const WbwDetailNoteWidget = ({ data, onChange }: IWidget) => {
  const onTextChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ) => {
    console.log("Change:", e.target.value);
    if (typeof onChange !== "undefined") {
      onChange({ field: "note", value: e.target.value });
    }
  };
  return (
    <>
      <TextArea
        defaultValue={data.note?.value ? data.note?.value : undefined}
        showCount
        maxLength={512}
        autoSize={{ minRows: 8, maxRows: 10 }}
        onChange={onTextChange}
      />
    </>
  );
};

export default WbwDetailNoteWidget;
