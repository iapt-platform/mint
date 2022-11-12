import { useIntl } from "react-intl";

import {
	Input,
	Button,
	Space,
	Badge,
	Tabs,
	Dropdown,
	Menu,
	Card,
	Typography,
	message,
} from "antd";

import {
	CloseOutlined,
	TranslationOutlined,
	BookOutlined,
	BlockOutlined,
	MoreOutlined,
	SaveOutlined,
	EditOutlined,
	IssuesCloseOutlined,
} from "@ant-design/icons";
import type { IUser } from "../auth/User";
import User from "../auth/User";
import { IChannel } from "../channel/Channel";
import TimeShow from "../utilities/TimeShow";
import MdView from "./MdView";
import { useState } from "react";
import { ISentenceRequst, ISentenceResponse } from "../api/Corpus";
import { put } from "../../request";

const { TextArea } = Input;
const { Text } = Typography;

interface ISentCellMenu {
	title: string;
	children?: React.ReactNode;
}
const SentCellMenu = ({ title, children }: ISentCellMenu) => {
	const [isHover, setIsHover] = useState(false);

	const menu = (
		<Menu
			onClick={(e) => {
				console.log(e);
			}}
			items={[
				{
					key: "en",
					label: "相关段落",
				},
				{
					key: "zh-Hans",
					label: "Nissaya",
				},
				{
					key: "zh-Hant",
					label: "相似句",
				},
			]}
		/>
	);

	return (
		<div
			onMouseEnter={() => {
				setIsHover(true);
			}}
			onMouseLeave={() => {
				setIsHover(false);
			}}
		>
			<div
				style={{
					marginTop: "-1.2em",
					position: "absolute",
					display: isHover ? "block" : "none",
				}}
			>
				<Dropdown overlay={menu} placement="bottomLeft">
					<Button
						type="primary"
						icon={<MoreOutlined />}
						size="small"
					/>
				</Dropdown>
			</div>
			{children}
		</div>
	);
};

interface ISentEditMenu {
	children?: React.ReactNode;
	onModeChange?: Function;
}
const SentEditMenu = ({ children, onModeChange }: ISentEditMenu) => {
	const [isHover, setIsHover] = useState(false);

	const menu = (
		<Menu
			onClick={(e) => {
				console.log(e);
			}}
			items={[
				{
					key: "en",
					label: "相关段落",
				},
				{
					key: "zh-Hans",
					label: "Nissaya",
				},
				{
					key: "zh-Hant",
					label: "相似句",
				},
			]}
		/>
	);

	return (
		<div
			onMouseEnter={() => {
				setIsHover(true);
			}}
			onMouseLeave={() => {
				setIsHover(false);
			}}
		>
			<div
				style={{
					marginTop: "-1.2em",
					right: "0",
					position: "absolute",
					display: isHover ? "block" : "none",
				}}
			>
				<Button
					icon={<EditOutlined />}
					size="small"
					onClick={() => {
						if (typeof onModeChange !== "undefined") {
							onModeChange("edit");
						}
					}}
				/>
				<Button icon={<IssuesCloseOutlined />} size="small" />
				<Dropdown overlay={menu} placement="bottomRight">
					<Button icon={<MoreOutlined />} size="small" />
				</Dropdown>
			</div>
			{children}
		</div>
	);
};

const SentTab = ({
	tranNum,
	nissayaNum,
	commNum,
	originNum,
	simNum,
}: IWidgetSentEditInner) => {
	const intl = useIntl();
	return (
		<Tabs
			size="small"
			items={[
				{
					label: (
						<Badge size="small" count={0}>
							<CloseOutlined />
						</Badge>
					),
					key: "close",
					children: <></>,
				},
				{
					label: (
						<Badge size="small" count={tranNum ? tranNum : 0}>
							<TranslationOutlined />
							{intl.formatMessage({
								id: "channel.type.translation.label",
							})}
						</Badge>
					),
					key: "tran",
					children: <div>译文</div>,
				},
				{
					label: (
						<Badge size="small" count={nissayaNum ? nissayaNum : 0}>
							<BookOutlined />
							{intl.formatMessage({
								id: "channel.type.nissaya.label",
							})}
						</Badge>
					),
					key: "nissaya",
					children: `2`,
				},
				{
					label: (
						<Badge size="small" count={commNum ? commNum : 0}>
							<BlockOutlined />
							{intl.formatMessage({
								id: "channel.type.commentary.label",
							})}
						</Badge>
					),
					key: "3",
					children: `3`,
				},
			]}
		/>
	);
};

export interface ISentence {
	content: string;
	html: string;
	book: number;
	para: number;
	wordStart: number;
	wordEnd: number;
	editor: IUser;
	channel: IChannel;
	updateAt: string;
}

interface ISentCellEditable {
	data: ISentence;
}
const SentCellEditable = ({ data }: ISentCellEditable) => {
	const intl = useIntl();
	const [value, setValue] = useState(data.content);
	const [saving, setSaving] = useState<boolean>(false);
	const save = () => {
		setSaving(true);
		put<ISentenceRequst, ISentenceResponse>(
			`/v2/sentence/${data.book}_${data.para}_${data.wordStart}_${data.wordEnd}_${data.channel.id}`,
			{
				book: data.book,
				para: data.para,
				wordStart: data.wordStart,
				wordEnd: data.wordEnd,
				channel: data.channel.id,
				content: value,
			}
		).then((json) => {
			setSaving(false);
			if (json.ok) {
				message.success(intl.formatMessage({ id: "flashes.success" }));
			} else {
				message.error(json.message);
			}
		});
	};
	return (
		<div>
			<TextArea
				value={value}
				onChange={(e) => setValue(e.target.value)}
				placeholder="Controlled autosize"
				autoSize={{ minRows: 3, maxRows: 5 }}
			/>
			<div style={{ display: "flex", justifyContent: "space-between" }}>
				<div>
					<span>
						<Text keyboard>esc</Text>=
						<Button size="small" type="link">
							cancel
						</Button>
					</span>
					<span>
						<Text keyboard>enter</Text>=
						<Button size="small" type="link">
							new line
						</Button>
					</span>
				</div>
				<div>
					<Text keyboard>Ctrl/⌘</Text>➕<Text keyboard>enter</Text>=
					<Button
						size="small"
						type="primary"
						icon={<SaveOutlined />}
						loading={saving}
						onClick={() => save()}
					>
						Save
					</Button>
				</div>
			</div>
		</div>
	);
};

interface ISentCell {
	data: ISentence;
}
const SentCell = ({ data }: ISentCell) => {
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

interface IWidgetSentEditInner {
	origin?: ISentence[];
	translation?: ISentence[];
	layout?: "row" | "column";
	tranNum?: number;
	nissayaNum?: number;
	commNum?: number;
	originNum: number;
	simNum?: number;
}
const SentEditInner = ({
	origin,
	translation,
	layout = "column",
	tranNum,
	nissayaNum,
	commNum,
	originNum,
	simNum,
}: IWidgetSentEditInner) => {
	return (
		<Card>
			<SentCellMenu title="blabla">
				<div style={{ display: "flex", flexDirection: layout }}>
					<div style={{ flex: "5", color: "#9f3a01" }}>
						{origin?.map((item, id) => {
							return <SentCell key={id} data={item} />;
						})}
					</div>
					<div style={{ flex: "5" }}>
						{translation?.map((item, id) => {
							return <SentCell key={id} data={item} />;
						})}
					</div>
				</div>
				<SentTab
					tranNum={tranNum}
					nissayaNum={nissayaNum}
					commNum={commNum}
					originNum={originNum}
					simNum={simNum}
				/>
			</SentCellMenu>
		</Card>
	);
};

interface IWidgetSentEdit {
	props: string;
}
const Widget = ({ props }: IWidgetSentEdit) => {
	const prop = JSON.parse(atob(props)) as IWidgetSentEditInner;
	return (
		<>
			<SentEditInner {...prop} />
		</>
	);
};

export default Widget;
