import { List, Card } from "antd";
import { Row, Col } from "antd";

export interface ICaseListData {
	word: string;
	count: number;
}
interface IWidgetCaseList {
	data: ICaseListData[];
}
const Widget = (prop: IWidgetCaseList) => {
	return (
		<Card title="Case List">
			<List
				footer={"共计30"}
				size="small"
				dataSource={prop.data}
				renderItem={(item) => (
					<List.Item>
						<Row>
							<Col>{item.word}</Col>
							<Col>{item.count}</Col>
						</Row>
					</List.Item>
				)}
			/>
		</Card>
	);
};

export default Widget;
