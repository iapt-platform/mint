import { Anchor } from "antd";
const { Link } = Anchor;
export interface IAnchorData {
	href: string;
	title: string;
	children?: IAnchorData[];
}
interface IWidgetDictList {
	data: IAnchorData[];
}
const Widget = (prop: IWidgetDictList) => {
	function GetLink(anchors: IAnchorData[]) {
		return anchors.map((it, id) => {
			return (
				<Link key={id} href={it.href} title={it.title}>
					{it.children ? GetLink(it.children) : ""}
				</Link>
			);
		});
	}

	return (
		<>
			<Anchor offsetTop={50}>{GetLink(prop.data)}</Anchor>
		</>
	);
};

export default Widget;
