import { ProCard } from "@ant-design/pro-components";
import { Button, Popover } from "antd";
import { SearchOutlined, EditOutlined } from "@ant-design/icons";
import { Typography } from "antd";

const { Text, Link } = Typography;

interface IWidgetTermCtl {
	word?: string;
	meaning?: string;
	meaning2?: string;
	channel?: string;
}
const TermCtl = ({ word, meaning, meaning2, channel }: IWidgetTermCtl) => {
	const userCard = (
		<>
			<ProCard
				title={word}
				style={{ maxWidth: 500, minWidth: 300 }}
				actions={[
					<Button type="link" icon={<SearchOutlined />}>
						更多
					</Button>,
					<Button type="link" icon={<SearchOutlined />}>
						详情
					</Button>,
					<Button type="link" icon={<EditOutlined />}>
						修改
					</Button>,
				]}
			>
				<div>详细内容</div>
			</ProCard>
		</>
	);
	const show = meaning ? meaning : word ? word : "unkow";
	return (
		<>
			<Popover content={userCard} placement="bottom">
				<Link>{show}</Link>
			</Popover>
			(<Text italic>{word}</Text>
			{","}
			<Text>{meaning2}</Text>)
		</>
	);
};

interface IWidgetTerm {
	props: string;
}
const Widget = ({ props }: IWidgetTerm) => {
	const prop = JSON.parse(decodeURI(props)) as IWidgetTermCtl;
	return (
		<>
			<TermCtl {...prop} />
		</>
	);
};

export default Widget;
