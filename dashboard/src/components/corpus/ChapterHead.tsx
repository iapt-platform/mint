import { Typography } from "antd";

const { Title, Text } = Typography;

export interface IChapterInfo {
	title: string;
	subTitle?: string;
	summary?: string;
	cover?: string;
}
interface IWidgetPaliChapterHeading {
	data: IChapterInfo;
}
const Widget = (prop: IWidgetPaliChapterHeading) => {
	return (
		<>
			<Title level={4}>{prop.data.title}</Title>
			<div>
				<Text type="secondary">{prop.data.subTitle ? prop.data.subTitle : ""}</Text>
			</div>
		</>
	);
};

export default Widget;
