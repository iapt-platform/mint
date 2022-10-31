import { Row, Col } from "antd";
import { Typography } from "antd";
import TimeShow from "../utilities/TimeShow";
import TocPath from "../corpus/TocPath";
import TagArea from "../tag/TagArea";
import type { TagNode } from "../tag/TagArea";
import type { ChannelInfoProps } from "../api/Channel";
import ChannelListItem from "../channel/ChannelListItem";

const { Title, Paragraph, Link } = Typography;

export interface ChapterData {
	Title: string;
	PaliTitle: string;
	Path: string;
	Book: number;
	Paragraph: number;
	Summary: string;
	Tag: TagNode[];
	Channel: ChannelInfoProps;
	CreatedAt: string;
	UpdatedAt: string;
	Hit: number;
	Like: number;
}

interface IWidgetChapterCard {
	data: ChapterData;
}

const Widget = (prop: IWidgetChapterCard) => {
	const path = JSON.parse(prop.data.Path);
	const tags = prop.data.Tag;
	const aa = {
		marginTop: "auto",
		marginBottom: "auto",
		display: "-webkit-box",
		//WebkitBoxOrient: "vertical",
		//WebkitLineClamp: 3,
		overflow: "hidden",
	};

	return (
		<>
			<Row>
				<Col span={3}>封面</Col>
				<Col span={21}>
					<Row>
						<Col span={16}>
							<Row>
								<Col>
									<Title level={5}>
										<Link>{prop.data.Title}</Link>
									</Title>
								</Col>
							</Row>
							<Row>
								<Col>{prop.data.PaliTitle}</Col>
							</Row>
							<Row>
								<Col>
									<TocPath data={path} />
								</Col>
							</Row>
						</Col>
						<Col span={8}>进度条</Col>
					</Row>
					<Row>
						<Col>
							<Paragraph>
								<div style={aa}>{prop.data.Summary}</div>
							</Paragraph>
						</Col>
					</Row>
					<Row>
						<Col span={16}>
							<TagArea data={tags} />
						</Col>
						<Col span={5}>
							<ChannelListItem data={prop.data.Channel} />
						</Col>
						<Col span={3}>
							<TimeShow time={prop.data.UpdatedAt} title="UpdatedAt" />
						</Col>
					</Row>
				</Col>
			</Row>
		</>
	);
};

export default Widget;
