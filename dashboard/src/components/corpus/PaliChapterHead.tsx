import { useState, useEffect } from "react";
import { message } from "antd";
import ChapterHead, { IChapterInfo } from "./ChapterHead";
import { IParagraph } from "./BookViewer";
import TocPath, { ITocPathNode } from "./TocPath";
import { IApiResponcePaliChapter } from "../api/Corpus";
import { ApiFetch } from "../../utils";

interface IWidgetPaliChapterHead {
	para: IParagraph;
	onChange?: Function;
}

const Widget = (prop: IWidgetPaliChapterHead) => {
	const defaultPathData: ITocPathNode[] = [
		{
			book: 98,
			paragraph: 55,
			title: "string;",
			paliTitle: "string;",
			level: 2,
		},
	];
	const [pathData, setPathData] = useState(defaultPathData);
	const [chapterData, setChapterData] = useState({ title: "" });
	useEffect(() => {
		console.log("palichapterlist useEffect");
		fetchData(prop.para);
	}, [prop.para]);

	function fetchData(para: IParagraph) {
		let url = `/palitext?view=paragraph&book=${para.book}&para=${para.para}`;
		ApiFetch(url).then(function (myJson) {
			console.log("ajex", myJson);
			const data = myJson as unknown as IApiResponcePaliChapter;
			let path: ITocPathNode[] = JSON.parse(data.data.path);
			path.push({
				book: data.data.book,
				paragraph: data.data.paragraph,
				title: data.data.toc,
				paliTitle: data.data.toc,
				level: data.data.level,
			});
			setPathData(path);
			const chapter: IChapterInfo = {
				title: data.data.toc,
				subTitle: data.data.toc,
			};
			setChapterData(chapter);
		});
	}
	return (
		<>
			<TocPath
				data={pathData}
				onChange={(e: IParagraph) => {
					message.success(e.book + ":" + e.para);
					fetchData(e);
					if (typeof prop.onChange !== "undefined") {
						prop.onChange(e);
					}
				}}
				link={"none"}
			/>
			<ChapterHead data={chapterData} />
		</>
	);
};

export default Widget;
