import { Space } from "antd";
import { Avatar } from "antd";
import type { ChannelInfoProps } from "../api/Channel";

type IWidgetChannelListItem = {
	data: ChannelInfoProps;
	showProgress?: boolean;
	showLike?: boolean;
};

const Widget = (props: IWidgetChannelListItem) => {
	const studioName = props.data.StudioName.slice(0, 2);
	return (
		<>
			<Space>
				<Avatar size="small">{studioName}</Avatar>
				<span>
					{props.data.ChannelName}@{props.data.StudioName}
				</span>
			</Space>
		</>
	);
};

export default Widget;
