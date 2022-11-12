import { Typography, Divider } from "antd";
import MdView from "../template/MdView";

const { Paragraph, Title, Text } = Typography;

export interface IWidgetArticleData {
	id?: string;
	title?: string;
	subTitle?: string;
	summary?: string;
	content?: string;
	created_at?: string;
	updated_at?: string;
}

const Widget = ({
	id,
	title,
	subTitle,
	summary,
	content,
	created_at,
	updated_at,
}: IWidgetArticleData) => {
	return (
		<>
			<Title level={1}>{title}</Title>
			<Text type="secondary">{subTitle}</Text>
			<Paragraph ellipsis={{ rows: 2, expandable: true, symbol: "more" }}>
				{summary}
			</Paragraph>
			<Divider />
			<div>
				<MdView html={content ? content : "none"} />
			</div>
		</>
	);
};

export default Widget;
