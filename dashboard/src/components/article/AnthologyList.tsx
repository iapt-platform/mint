import { useState, useEffect } from "react";
import { List } from "antd";
import AnthologyCard from "./AnthologyCard";
import type { IAnthologyData } from "./AnthologyCard";
import type { IAnthologyListApiResponse } from "../api/Article";

const defaultData: IAnthologyData[] = [];

const Widget = () => {
	const [tableData, setTableData] = useState(defaultData);

	useEffect(() => {
		console.log("useEffect");
		fetchData();
	}, [setTableData]);

	function fetchData() {
		let url = `http://127.0.0.1:8000/api/v2/anthology?view=public`;
		fetch(url)
			.then(function (response) {
				console.log("ajex:", response);
				return response.json();
			})
			.then(function (myJson) {
				console.log("ajex", myJson);

				let newTree: IAnthologyData[] = myJson.data.rows.map((item: IAnthologyListApiResponse) => {
					return {
						id: item.uid,
						title: item.title,
						subTitle: item.subtitle,
						summary: item.summary,
						articles: item.article_list.map((al) => {
							return {
								id: al.article,
								title: al.title,
								subTitle: "",
								summary: "",
								created_at: "",
								updated_at: "",
							};
						}),
						studio: item.studio,
						created_at: item.created_at,
						updated_at: item.updated_at,
					};
				});
				setTableData(newTree);
			});
	}

	return (
		<List
			itemLayout="vertical"
			size="large"
			dataSource={tableData}
			renderItem={(item) => (
				<List.Item>
					<AnthologyCard data={item} />
				</List.Item>
			)}
		/>
	);
};

export default Widget;
