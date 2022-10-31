import { useState, useEffect } from "react";
import { Typography } from "antd";
import ReactMarkdown from "react-markdown";

import type { IAnthologyData } from "./AnthologyCard";
import type {
	IAnthologyListApiResponse,
	IAnthologyListApiResponse2,
} from "../api/Article";
import TocTree from "./TocTree";
import { get } from "../../request";

const { Title, Text } = Typography;

interface IWidgetAnthologyDetail {
	aid: string;
	channels?: string[];
}

const defaultData: IAnthologyData = {
	id: "",
	title: "",
	subTitle: "",
	summary: "",
	articles: [],
	studio: {
		id: "",
		studioName: "",
		nickName: "",
		avatar: "",
	},
	created_at: "",
	updated_at: "",
};

const Widget = (prop: IWidgetAnthologyDetail) => {
	const [tableData, setTableData] = useState(defaultData);

	useEffect(() => {
		console.log("useEffect");
		fetchData(prop.aid);
	}, [prop.aid]);

	function fetchData(id: string) {
		get(`/v2/anthology/${id}`)
			.then((response) => {
				const json = response as unknown as IAnthologyListApiResponse2;

				const item: IAnthologyListApiResponse = json.data;
				let newTree: IAnthologyData = {
					id: item.uid,
					title: item.title,
					subTitle: item.subtitle,
					summary: item.summary,
					articles: item.article_list.map((al) => {
						return {
							key: al.article,
							title: al.title,
							level: parseInt(al.level),
						};
					}),
					studio: item.studio,
					created_at: item.created_at,
					updated_at: item.updated_at,
				};
				setTableData(newTree);
				console.log("toc", newTree.articles);
			})
			.catch((error) => {
				console.error(error);
			});
	}
	return (
		<>
			<Title level={4}>{tableData.title}</Title>
			<div>
				<Text type="secondary">{tableData.subTitle}</Text>
			</div>
			<div>
				<ReactMarkdown>{tableData.summary}</ReactMarkdown>
			</div>
			<Title level={5}>目录</Title>

			<TocTree treeData={tableData.articles} />
		</>
	);
};

export default Widget;
