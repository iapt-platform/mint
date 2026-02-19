import { Select } from "antd";

const { Option } = Select;

interface IWidget {
  onSelect?: Function;
}
const ChpterFilterProgressWidget = ({ onSelect }: IWidget) => {
  return (
    <Select
      style={{ width: 100 }}
      placeholder="完成度"
      defaultValue={["90"]}
      onChange={(value: string[]) => {
        console.log(`selected`, value);
        if (typeof onSelect !== "undefined") {
          onSelect(value);
        }
      }}
    >
      <Option key="0.9">90%</Option>
      <Option key="0.8">80%</Option>
      <Option key="0.7">70%</Option>
      <Option key="0.6">60%</Option>
      <Option key="0.5">50%</Option>
      <Option key="0.01">1%</Option>
    </Select>
  );
};

export default ChpterFilterProgressWidget;
