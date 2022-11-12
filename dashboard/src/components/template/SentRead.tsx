import { Tooltip, Button } from "antd";
import MdView from "./MdView";

interface IWidgetSentReadFrame {
	origin?: string[];
	translation?: string[];
	layout?: "row" | "column";
	sentId?: string;
}
const SentReadFrame = ({
	origin,
	translation,
	layout = "column",
	sentId,
}: IWidgetSentReadFrame) => {
	return (
		<Tooltip
			placement="topLeft"
			color="white"
			title={
				<Button type="link" size="small">
					aa
				</Button>
			}
		>
			<div style={{ display: "flex", flexDirection: layout }}>
				<div style={{ flex: "5", color: "#9f3a01" }}>
					{origin?.map((item, id) => {
						return <MdView key={id} html={item} />;
					})}
				</div>
				<div style={{ flex: "5" }}>
					{translation?.map((item, id) => {
						return <MdView key={id} html={item} />;
					})}
				</div>
			</div>
		</Tooltip>
	);
};

interface IWidgetTerm {
	props: string;
}
const Widget = ({ props }: IWidgetTerm) => {
	const prop = JSON.parse(atob(props)) as IWidgetSentReadFrame;
	return (
		<>
			<SentReadFrame {...prop} />
		</>
	);
};

export default Widget;
