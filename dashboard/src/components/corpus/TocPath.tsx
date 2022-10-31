import { Link } from "react-router-dom";
import { Breadcrumb } from "antd";

export interface ITocPathNode {
	book: number;
	paragraph: number;
	title: string;
	paliTitle?: string;
	level: number;
}

export declare type ELinkType = "none" | "blank" | "self";

interface IWidgetTocPath {
	data: ITocPathNode[];
	link?: ELinkType;
	channel?: string;
	onChange?: Function;
}
const Widget = (prop: IWidgetTocPath) => {
	const link: ELinkType = prop.link ? prop.link : "blank";
	const path = prop.data.map((item, id) => {
		const linkChapter = `/article/index.php?view=chapter&book=${item.book}&par=${item.paragraph}`;
		let oneItem = <></>;
		switch (link) {
			case "none":
				oneItem = <>{item.title}</>;
				break;
			case "blank":
				oneItem = <Link to={linkChapter}>{item.title}</Link>;
				break;
			case "self":
				oneItem = <Link to={linkChapter}>{item.title}</Link>;
				break;
		}
		return (
			<Breadcrumb.Item
				onClick={() => {
					if (typeof prop.onChange !== "undefined") {
						prop.onChange({ book: item.book, para: item.paragraph });
					}
				}}
				key={id}
			>
				{oneItem}
			</Breadcrumb.Item>
		);
	});
	return (
		<>
			<Breadcrumb>{path}</Breadcrumb>
		</>
	);
};

export default Widget;
