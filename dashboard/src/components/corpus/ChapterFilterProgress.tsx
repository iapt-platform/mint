import { Select } from "antd";
import React from "react";

const { Option } = Select;

const children: React.ReactNode[] = [];
for (let i = 10; i < 36; i++) {
	children.push(<Option key={i.toString(36) + i}>{i.toString(36) + i}</Option>);
}

const handleChange = (value: string[]) => {
	console.log(`selected ${value}`);
};
const Widget = () => {
	return (
		<Select style={{ width: 100 }} placeholder="完成度" defaultValue={["90"]} onChange={handleChange}>
			<Option key="0.9">90%</Option>
			<Option key="0.8">80%</Option>
			<Option key="0.7">70%</Option>
			<Option key="0.6">60%</Option>
			<Option key="0.5">50%</Option>
			<Option key="0.01">1%</Option>
		</Select>
	);
};

export default Widget;
