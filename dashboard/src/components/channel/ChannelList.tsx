import { useState, useEffect } from "react";
import { List } from "antd";
import ChannelListItem from "./ChannelListItem";
import type { ChannelInfoProps } from "../api/Channel";
import { IApiResponseChannelList } from "../api/Corpus";
import { get } from "../../request";

export interface ChannelFilterProps {
	chapterProgress: number;
	lang: string;
	channelType: string;
}
interface IWidgetChannelList {
	filter?: ChannelFilterProps;
}
const defaultChannelFilterProps: ChannelFilterProps = {
	chapterProgress: 0.9,
	lang: "zh",
	channelType: "translation",
};

const Widget = ({ filter = defaultChannelFilterProps }: IWidgetChannelList) => {
	const defualt: ChannelInfoProps[] = [];
	const [tableData, setTableData] = useState(defualt);

	useEffect(() => {
		console.log("palichapterlist useEffect");
		let url = `/v2/progress?view=channel&channel_type=${filter.channelType}&lang=${filter.lang}&progress=${filter.chapterProgress}`;
		get(url).then(function (myJson) {
			console.log("ajex", myJson);
			const data = myJson as unknown as IApiResponseChannelList;
			const newData: ChannelInfoProps[] = data.data.rows.map((item) => {
				return {
					ChannelName: item.channel.name,
					ChannelId: item.channel.uid,
					ChannelType: item.channel.type,
					StudioName: "V",
					StudioId: "123",
					StudioType: "p",
				};
			});
			setTableData(newData);
		});
	}, [filter]);
	return (
		<>
			<h3>Channel</h3>
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
		</>
	);
};

export default Widget;
