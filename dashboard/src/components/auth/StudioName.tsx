import { Avatar, Space } from "antd";

export interface IStudio {
	id: string;
	nickName: string;
	studioName: string;
	avatar: string;
}
interface IWidghtStudio {
	data: IStudio;
	onClick?: Function;
}
const Widget = (prop: IWidghtStudio) => {
	// TODO
	const name = prop.data.nickName.slice(0, 1);
	return (
		<Space
			onClick={() => {
				if (typeof prop.onClick !== "undefined") {
					prop.onClick(prop.data.studioName);
				}
			}}
		>
			<Avatar size="small">{name}</Avatar>
			{prop.data.nickName}
		</Space>
	);
};

export default Widget;
