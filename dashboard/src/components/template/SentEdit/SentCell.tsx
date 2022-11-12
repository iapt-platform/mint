import { Space } from "antd";
import { useState } from "react";
import User from "../../auth/User";
import TimeShow from "../../utilities/TimeShow";
import { ISentence } from "../SentEdit";
import SentEditMenu from "./SentEditMenu";
import SentCellEditable from "./SentCellEditable";
import MdView from "../MdView";

interface ISentCell {
	data: ISentence;
}
const Widget = ({ data }: ISentCell) => {
	const [isEditMode, setIsEditMode] = useState(false);
	return (
		<SentEditMenu
			onModeChange={(mode: string) => {
				if (mode === "edit") {
					setIsEditMode(true);
				}
			}}
		>
			<div style={{ display: isEditMode ? "none" : "block" }}>
				<MdView html={data.html} />
			</div>
			<div style={{ display: isEditMode ? "block" : "none" }}>
				<SentCellEditable data={data} />
			</div>
			<div>
				<Space>
					<User {...data.editor} />
					<span>updated</span>
					<TimeShow time={data.updateAt} title="UpdatedAt" />
				</Space>
			</div>
		</SentEditMenu>
	);
};

export default Widget;
