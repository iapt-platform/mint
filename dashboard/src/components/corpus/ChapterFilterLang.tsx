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
		<Select
			mode="multiple"
			allowClear
			style={{ minWidth: 100 }}
			placeholder="Language"
			defaultValue={[]}
			onChange={handleChange}
		>
			{children}
		</Select>
	);
};

export default Widget;
