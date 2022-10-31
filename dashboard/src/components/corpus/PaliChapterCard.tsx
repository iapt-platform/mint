import { Row, Col } from "antd";
import { Typography } from "antd";
import TocPath from "./TocPath";

const { Title, Link } = Typography;

export interface IPaliChapterData {
	Title: string;
	PaliTitle: string;
	Path: string;
	Book: number;
	Paragraph: number;
}

interface IWidgetPaliChapterCard {
	data: IPaliChapterData;
	onTitleClick?: Function;
}

const Widget = (prop: IWidgetPaliChapterCard) => {
	const path = JSON.parse(prop.data.Path);

	return (
		<>
			<Row>
				<Col span={3}>封面</Col>
				<Col span={21}>
					<Row>
						<Col span={16}>
							<Row>
								<Col>
									<Title
										level={5}
										onClick={(e) => {
											if (typeof prop.onTitleClick !== "undefined") {
												prop.onTitleClick(e);
											}
										}}
									>
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
						<Col></Col>
					</Row>
					<Row>
						<Col span={16}></Col>
					</Row>
				</Col>
			</Row>
		</>
	);
};

export default Widget;
