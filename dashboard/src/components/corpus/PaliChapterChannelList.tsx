import { useState, useEffect } from "react";
import { ApiFetch } from "../../utils";
import { IApiResponseChapterChannelList } from "../api/Corpus";
import { IParagraph } from "./BookViewer";
import ChapterInChannel, { IChapterChannelData } from "./ChapterInChannel";

interface IWidgetPaliChapterChannelList {
	para: IParagraph;
}
const defaultData: IChapterChannelData[] = [];
const Widget = (prop: IWidgetPaliChapterChannelList) => {
	const [tableData, setTableData] = useState(defaultData);

	useEffect(() => {
		console.log("palichapterlist useEffect");
		let url = `/progress?view=chapter_channels&book=${prop.para.book}&par=${prop.para.para}`;
		ApiFetch(url).then(function (myJson) {
			console.log("ajex", myJson);
			const data = myJson as unknown as IApiResponseChapterChannelList;
			const newData: IChapterChannelData[] = data.data.rows.map((item) => {
				return {
					channel: {
						ChannelName: item.channel.name,
						ChannelId: item.channel.uid,
						ChannelType: item.channel.type,
						StudioName: "V",
						StudioId: "123",
						StudioType: "p",
					},
					progress: Math.ceil(item.progress * 100),
					hit: item.views,
					like: 0,
					updatedAt: item.updated_at,
				};
			});
			setTableData(newData);
		});
	}, [prop.para]);

	return (
		<>
			<ChapterInChannel data={tableData} />
		</>
	);
};

export default Widget;
