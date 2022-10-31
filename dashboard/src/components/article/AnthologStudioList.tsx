import { useState, useEffect } from "react";
import { List, Space, Card } from "antd";
import StudioNmae from "../auth/StudioName";
import type { IAnthologyStudioListApiResponse } from "../api/Article";
import type { IStudioApiResponse } from "../api/Auth";

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

	useEffect(() => {
		console.log("useEffect");
		fetchData();
	}, [setTableData]);

	function fetchData() {
		let url = `http://127.0.0.1:8000/api/v2/anthology?view=studio_list`;
		fetch(url)
			.then(function (response) {
				console.log("ajex:", response);
				return response.json();
			})
			.then(function (myJson) {
				console.log("ajex", myJson);

				let newTree: IAnthologyStudioData[] = myJson.data.rows.map((item: IAnthologyStudioListApiResponse) => {
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
						<Space>
							<StudioNmae data={item.studio} />
							<span>({item.count})</span>
						</Space>
					</List.Item>
				)}
			/>
		</Card>
	);
};

export default Widget;
