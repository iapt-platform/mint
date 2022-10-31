import { useState, useEffect } from "react";

import PaliChapterChannelList from "./PaliChapterChannelList";
import PaliChapterListByPara from "./PaliChapterListByPara";
import PaliChapterHead from "./PaliChapterHead";
import { IChapterClickEvent } from "./PaliChapterList";

export interface IParagraph {
	book: number;
	para: number;
}

interface IWidgetBookViewer {
	para: IParagraph;
	onChange?: Function;
}
const Widget = (prop: IWidgetBookViewer) => {
	const [para, setPara] = useState(prop.para);
	useEffect(() => {
		if (typeof prop.onChange !== "undefined") {
			prop.onChange(para);
		}
	}, [para]);

	useEffect(() => {
		setPara(prop.para);
	}, [prop.para]);
	return (
		<>
			<PaliChapterHead
				onChange={(e: IParagraph) => {
					setPara(e);
				}}
				para={para}
			/>
			<PaliChapterChannelList para={para} />
			<PaliChapterListByPara
				para={para}
				onChapterClick={(e: IChapterClickEvent) => {
					setPara({ book: e.para.Book, para: e.para.Paragraph });
					console.log("PaliChapterListByPara", "onchange", e);
				}}
			/>
		</>
	);
};

export default Widget;
