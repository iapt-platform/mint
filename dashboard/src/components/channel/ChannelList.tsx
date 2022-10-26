import { useState } from "react";
import { List } from "antd";
import ChannelListItem from "./ChannelListItem";
import type { ChannelInfoProps } from "../api/Channel";
import { Collapse } from "antd";

const { Panel } = Collapse;

export type ChannelFilterProps = {
	ChapterProgress: number;
	lang: string;
	ChannelType: string;
};
type IWidgetChannelList = {
	props?: ChannelFilterProps;
};
const defaultChannelFilterProps: ChannelFilterProps = {
	ChapterProgress: 0.9,
	lang: "en",
	ChannelType: "translation",
};

const Widget = ({ props = defaultChannelFilterProps }: IWidgetChannelList) => {
	//const [tableData, setTableData] = useState();
	//: ChannelInfoProps[]
	const tableData: ChannelInfoProps[] = [
		{
			ChannelName: "正式版",
			ChannelId: "344",
			ChannelType: "translation",
			StudioName: "visuddhinadna",
			StudioId: "2333",
			StudioType: "org",
		},
		{
			ChannelName: "中文译文",
			ChannelId: "2345",
			ChannelType: "translation",
			StudioName: "Kosalla",
			StudioId: "1234",
			StudioType: "people",
		},
	];
	return (
		<Collapse defaultActiveKey={["1"]} expandIconPosition="start">
			<Panel header="版本" key="1">
				<List
					itemLayout="vertical"
					size="large"
					dataSource={tableData}
					renderItem={(item) => (
						<List.Item>
							<ChannelListItem data={item} />
						</List.Item>
					)}
				/>
			</Panel>
		</Collapse>
	);
};

export default Widget;
