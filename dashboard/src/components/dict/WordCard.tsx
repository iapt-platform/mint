/* eslint-disable jsx-a11y/anchor-is-valid */
import { Typography } from "antd";
import IWidgetGrammarPop from "./GrammarPop";
import WordCardByDict from "./WordCardByDict";

import type { IWordByDict } from "./WordCardByDict";

const { Title, Text } = Typography;

export interface IWidgetWordCardData {
	word: string;
	factors: string;
	parents: string;
	case: string[];
	anchor: string;
	dict: IWordByDict[];
}
interface IWidgetWordCard {
	data: IWidgetWordCardData;
}
const Widget = (prop: IWidgetWordCard) => {
	const caseList = prop.data.case.map((element) => {
		return element.split("|").map((it, id) => {
			if (it.slice(0, 1) === "@") {
				const [showText, keyText] = it.slice(1).split("-");
				return <IWidgetGrammarPop key={id} gid={keyText} text={showText} />;
			} else {
				return <span key={id * 200}>{it}</span>;
			}
		});
	});
	return (
		<>
			<Title level={4} id={prop.data.anchor}>
				{prop.data.word}
			</Title>

			<div>
				<Text>{prop.data.factors}</Text>
			</div>
			<div>
				<Text>{prop.data.parents}</Text>
			</div>
			<div>
				<Text>{caseList}</Text>
			</div>
			<div>
				{prop.data.dict.map((it, id) => {
					return <WordCardByDict key={id} data={it} />;
				})}
			</div>
		</>
	);
};

export default Widget;
