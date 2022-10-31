import { Select } from "antd";
import React from "react";

const { Option } = Select;

const children: React.ReactNode[] = [];
for (let i = 1; i < 5; i++) {
	children.push(<Option key={i.toString(5) + i}>{i.toString(5) + i}</Option>);
}

const handleChange = (value: string[]) => {
	console.log(`selected ${value}`);
};
const Widget = () => {
	return (
		<Select style={{ width: 100 }} allowClear placeholder="Type" defaultValue={[]} onChange={handleChange}>
			{children}
		</Select>
	);
};

export default Widget;
