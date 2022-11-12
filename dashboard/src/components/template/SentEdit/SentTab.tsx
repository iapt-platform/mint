import { Badge, Tabs } from "antd";
import {
	TranslationOutlined,
	BookOutlined,
	CloseOutlined,
	BlockOutlined,
} from "@ant-design/icons";
import { useIntl } from "react-intl";
import { IWidgetSentEditInner } from "../SentEdit";
const Widget = ({
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

export default Widget;
