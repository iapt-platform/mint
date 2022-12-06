import { useState, useEffect } from "react";
import DictContent from "./DictContent";
import type {
	IWidgetDictContentData,
	IApiDictContentData,
} from "./DictContent";
import { get } from "../../request";

interface IWidgetDictSearch {
	word: string | undefined;
}

const Widget = (prop: IWidgetDictSearch) => {
	const defaultData: IWidgetDictContentData = {
		dictlist: [],
		words: [],
		caselist: [],
	};
	const [tableData, setTableData] = useState(defaultData);

	useEffect(() => {
		console.log("useEffect");
		const url = `/v2/dict?word=${prop.word}`;
		console.log("url", url);
		get(url)
			.then((response) => {
				const json = response as unknown as IApiDictContentData;
				console.log("data", json);
				setTableData(json.data);
			})
			.catch((error) => {
				console.error(error);
			});
	}, [prop.word, setTableData]);

	return (
		<>
			<DictContent data={tableData} />
		</>
	);
};

export default Widget;
