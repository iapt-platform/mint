import { Select } from "antd";
const { Option } = Select;

interface IWidget {
  style?: string;
  onChange?: Function;
}
const Widget = ({ style = "default", onChange }: IWidget) => {
  return (
    <Select
      defaultValue={style}
      style={{ width: 90 }}
      loading={false}
      onChange={(value: string) => {
        if (typeof onChange !== "undefined") {
          onChange(value);
        }
      }}
    >
      <Option value="default">Default</Option>
      <Option value="cscd">CSCD</Option>
    </Select>
  );
};

export default Widget;
