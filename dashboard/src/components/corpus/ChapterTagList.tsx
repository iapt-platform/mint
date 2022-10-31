import { message, Tag, Button } from "antd";
import { useState, useEffect } from "react";
import { ApiFetch } from "../../utils";
import { IApiChapterTag, IApiResponseChapterTagList } from "../api/Corpus";

interface ITagData {
	title: string;
	key: string;
}
interface IWidgetChapterTagList {
	max?: number;
	onTagClick: Function;
}
const Widget = (prop: IWidgetChapterTagList) => {
	const defaultData: ITagData[] = [];
	const [tableData, setTableData] = useState(defaultData);

	useEffect(() => {
		console.log("useEffect");
		fetchData();
	}, []);

	function fetchData() {
		ApiFetch(`/progress?view=chapter-tag`)
			.then((response) => {
				const json = response as unknown as IApiResponseChapterTagList;
				const tags: IApiChapterTag[] = json.data.rows;
				let newTags: ITagData[] = tags.map((item) => {
					return {
						key: item.name,
						title: `${item.name}(${item.count})`,
					};
				});
				setTableData(newTags);
			})
			.catch((error) => {
				message.error(error);
			});
	}
	let iTag = prop.max ? prop.max : tableData.length;
	if (iTag > tableData.length) {
		iTag = tableData.length;
	}
	return (
		<>
			{tableData.map((item, id) => {
				return (
					<Tag
						key={id}
						onClick={() => {
							if (typeof prop.onTagClick !== "undefined") {
								prop.onTagClick(item.key);
							}
						}}
					>
						<Button type="link">{item.title}</Button>
					</Tag>
				);
			})}
		</>
	);
};

export default Widget;
