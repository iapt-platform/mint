import { useState, useEffect } from "react";
import { List, Space, Card } from "antd";
import StudioName from "../auth/StudioName";
import type { IAnthologyStudioListApiResponse } from "../api/Article";
import type { IStudioApiResponse } from "../api/Auth";
import { get } from "../../request";
import { Link, useNavigate } from "react-router-dom";

const defaultData: IAnthologyStudioData[] = [];

interface IAnthologyStudioData {
	count: number;
	studio: IStudioApiResponse;
}
/*
interface IWidgetAnthologyList {
	data: IAnthologyData[];
}
*/
const Widget = () => {
	const [tableData, setTableData] = useState(defaultData);
	const navigate = useNavigate();
	useEffect(() => {
		console.log("useEffect");
		fetchData();
	}, []);

	function fetchData() {
		let url = `/v2/anthology?view=studio_list`;
		get(url).then(function (myJson) {
			console.log("ajex", myJson);
			const json = myJson as unknown as IAnthologyStudioListApiResponse;
			let newTree: IAnthologyStudioData[] = json.data.rows.map((item) => {
				return {
					count: item.count,
					studio: item.studio,
				};
			});
			setTableData(newTree);
		});
	}

	return (
		<Card title="作者">
			<List
				itemLayout="vertical"
				size="large"
				dataSource={tableData}
				renderItem={(item) => (
					<List.Item>
						<Link to={`/blog/${item.studio.studioName}/anthology`}>
							<Space>
								<StudioName data={item.studio} />
								<span>({item.count})</span>
							</Space>
						</Link>
					</List.Item>
				)}
			/>
		</Card>
	);
};

export default Widget;
