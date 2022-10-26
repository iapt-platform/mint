import { Col, Layout, Progress, Row, Space } from "antd";
import { Typography } from "antd";
import { LikeOutlined, EyeOutlined } from "@ant-design/icons";
import { ChannelInfoProps } from "../api/Channel";
import ChannelListItem from "../channel/ChannelListItem";
import TimeShow from "../utilities/TimeShow";

const { Text } = Typography;

export interface IChapterChannelData {
	channel: ChannelInfoProps;
	progress: number;
	hit: number;
	like: number;
	updatedAt: string;
}
interface IWidgetChapterInChannel {
	data: IChapterChannelData[];
}
const Widget = (prop: IWidgetChapterInChannel) => {
	const view = prop.data.map((item, id) => {
		return (
			<Layout key={id}>
				<Row>
					<Col span={5}>
						<ChannelListItem data={item.channel} />
					</Col>
					<Col span={5}>
						<Progress percent={item.progress} size="small" />
					</Col>
					<Col span={8}></Col>
				</Row>

				<Text type="secondary">
					<Space style={{ paddingLeft: "2em" }}>
						<EyeOutlined />
						{item.hit} | <LikeOutlined />
						{item.like} |
						<TimeShow time={item.updatedAt} title={item.updatedAt} />
					</Space>
				</Text>
			</Layout>
		);
	});
	return <>{view}</>;
};

export default Widget;
