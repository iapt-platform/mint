import { useIntl } from "react-intl";
import { Input } from "antd";

import { IWbw } from "./WbwWord";

const { TextArea } = Input;

interface IWidget {
  data: IWbw;
  onChange?: Function;
}
const Widget = ({ data, onChange }: IWidget) => {
  const intl = useIntl();
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
        defaultValue={data.note?.value}
        showCount
        maxLength={512}
        autoSize={{ minRows: 8, maxRows: 10 }}
        onChange={onTextChange}
      />
    </>
  );
};

export default Widget;
