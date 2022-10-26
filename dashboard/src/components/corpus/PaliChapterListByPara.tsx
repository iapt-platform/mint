import { useState, useEffect } from "react";
import { ApiFetch } from "../../utils";
import { IApiResponcePaliChapterList } from "../api/Corpus";
import { IParagraph } from "./BookViewer";
import { IPaliChapterData } from "./PaliChapterCard";
import PaliChapterList, { IChapterClickEvent } from "./PaliChapterList";

interface IWidgetPaliChapterListByPara {
	para: IParagraph;
	onChapterClick?: Function;
}
const defaultData: IPaliChapterData[] = [];
const Widget = (prop: IWidgetPaliChapterListByPara) => {
	const [tableData, setTableData] = useState(defaultData);

	useEffect(() => {
		console.log("palichapterlist useEffect");
		let url = `/palitext?view=chapter_children&book=${prop.para.book}&para=${prop.para.para}`;
		ApiFetch(url).then(function (myJson) {
			console.log("ajex", myJson);
			const data = myJson as unknown as IApiResponcePaliChapterList;
			let newTree: IPaliChapterData[] = data.data.rows.map((item) => {
				return {
					Title: item.toc,
					PaliTitle: item.toc,
					Path: item.path,
					Book: item.book,
					Paragraph: item.paragraph,
				};
			});
			setTableData(newTree);
		});
	}, [prop.para]);

	return (
		<>
			<PaliChapterList
				onChapterClick={(e: IChapterClickEvent) => {
					if (prop.onChapterClick) {
						prop.onChapterClick(e);
					}
				}}
				data={tableData}
			/>
		</>
	);
};

export default Widget;
