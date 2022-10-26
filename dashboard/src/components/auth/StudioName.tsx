import { Avatar, Space } from "antd";

export interface IStudio {
	id: string;
	name: string;
	avatar: string;
}
interface IWidghtStudio {
	data: IStudio;
}
const Widget = (prop: IWidghtStudio) => {
	// TODO
	const name = prop.data.name.slice(0, 1);
	return (
		<Space>
			<Avatar size="small">{name}</Avatar>
			{prop.data.name}
		</Space>
	);
};

export default Widget;
