import { useState, useEffect } from "react";
import { List } from "antd";
import ChapterCard from "./ChapterCard";
import type { ChapterData } from "./ChapterCard";
import type { ChannelFilterProps } from "../channel/ChannelList";

const defaultChannelFilterProps: ChannelFilterProps = {
	chapterProgress: 0.9,
	lang: "en",
	channelType: "translation",
};

interface IWidgetChannelList {
	filter?: ChannelFilterProps;
	tags?: string[];
}
const defaultData: ChapterData[] = [];

interface IChapterData {
	title: string;
	toc: string;
	book: number;
	para: number;
	path: string;
	tags: string;
	channel: { name: string; owner_uid: string };
	summary: string;
	view: number;
	like: number;
	created_at: string;
	updated_at: string;
}

const Widget = ({ filter = defaultChannelFilterProps, tags = [] }: IWidgetChannelList) => {
	const [tableData, setTableData] = useState(defaultData);

	useEffect(() => {
		console.log("useEffect");

		fetchData(filter, tags);
	}, [tags, filter]);

	function fetchData(filter: ChannelFilterProps, tags: string[]) {
		const strTags = tags.length > 0 ? "&tags=" + tags.join() : "";
		console.log("strtags", strTags);
		let url = `http://127.0.0.1:8000/api/v2/progress?view=chapter${strTags}`;
		fetch(url)
			.then(function (response) {
				console.log("ajex:", response);
				return response.json();
			})
			.then(function (myJson) {
				console.log("ajex", myJson);
				let newTree = myJson.data.rows.map((item: IChapterData) => {
					return {
						Title: item.title,
						PaliTitle: item.toc,
						Path: item.path,
						Book: item.book,
						Paragraph: item.para,
						Summary: item.summary,
						Tag: item.tags,
						Channel: {
							ChannelName: item.channel.name,
							ChannelId: "",
							ChannelType: "translation",
							StudioName: item.channel.name,
							StudioId: item.channel.owner_uid,
							StudioType: "",
						},
						CreatedAt: item.created_at,
						UpdatedAt: item.updated_at,
						Hit: item.view,
						Like: item.like,
						ChannelInfo: "string",
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
					<ChapterCard data={item} />
				</List.Item>
			)}
		/>
	);
};

export default Widget;
