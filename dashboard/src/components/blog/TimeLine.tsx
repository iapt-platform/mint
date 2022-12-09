import { Timeline } from "antd";

interface IAuthorTimeLine {
	lable: string;
	content: string;
	type: string;
}
const Widget = () => {
	const data: IAuthorTimeLine[] = [
		{
			lable: "2015-09-1",
			content: "Technical testing",
			type: "translation",
		},
		{
			lable: "2015-09-1",
			content: "Technical testing",
			type: "translation",
		},
		{
			lable: "2015-09-1",
			content: "Technical testing",
			type: "translation",
		},
		{
			lable: "2015-09-1",
			content: "Technical testing",
			type: "translation",
		},
	];

	return (
		<>
			<Timeline mode={"left"} style={{ width: "100%" }}>
				{data.map((item, id) => {
					return (
						<Timeline.Item key={id} label={item.lable}>
							{item.content}
						</Timeline.Item>
					);
				})}
			</Timeline>
		</>
	);
};

export default Widget;
