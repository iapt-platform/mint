import { useState, useEffect } from "react";
import { ApiFetch } from "../../utils";
import { IApiResponcePaliChapterList } from "../api/Corpus";
import { IPaliChapterData } from "./PaliChapterCard";
import PaliChapterList, { IChapterClickEvent } from "./PaliChapterList";

interface IWidgetPaliChapterListByTag {
	tag: string[];
	onChapterClick?: Function;
}
const defaultData: IPaliChapterData[] = [];
const Widget = (prop: IWidgetPaliChapterListByTag) => {
	const [tableData, setTableData] = useState(defaultData);

	useEffect(() => {
		console.log("palichapterlist useEffect");
		let url = `/palitext?view=chapter&tags=${prop.tag.join()}`;
		console.log("tag url", url);
		ApiFetch(url).then(function (myJson) {
			console.log("ajex", myJson);
			const data = myJson as unknown as IApiResponcePaliChapterList;
			let newTree: IPaliChapterData[] = data.data.rows.map((item) => {
				return {
					Title: item.title,
					PaliTitle: item.title,
					Path: item.path,
					Book: item.book,
					Paragraph: item.paragraph,
				};
			});
			setTableData(newTree);
		});
	}, [prop.tag]);

	return (
		<>
			<PaliChapterList
				data={tableData}
				onChapterClick={(e: IChapterClickEvent) => {
					if (typeof prop.onChapterClick !== "undefined") {
						prop.onChapterClick(e);
					}
				}}
			/>
		</>
	);
};

export default Widget;
