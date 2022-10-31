import { Tag } from "antd";

export interface TagNode {
	id: string;
	name: string;
	description?: string;
}
interface IWidgetTagArea {
	data: TagNode[];
}
const Widget = (prop: IWidgetTagArea) => {
	// TODO
	const tags = prop.data.map((item, id) => {
		return (
			<Tag color="green" key={id}>
				{item.name}
			</Tag>
		);
	});
	return <>{tags}</>;
};

export default Widget;
