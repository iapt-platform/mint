import { Dropdown, Button } from "antd";
import { GlobalOutlined } from "@ant-design/icons";
import type { MenuProps } from "antd";

const items: MenuProps["items"] = [
	{
		key: "en",
		label: "English",
	},
	{
		key: "zh-Hans",
		label: "简体中文",
	},
	{
		key: "zh-Hant",
		label: "繁体中文",
	},
];
const Widget = () => {
	// TODO
	return (
		<Dropdown menu={{ items }} placement="bottomRight">
			<Button
				ghost
				icon={<GlobalOutlined />}
				onClick={(e) => {
					console.log("click ", e);
					e.preventDefault();
				}}
			>
				简体中文
			</Button>
		</Dropdown>
	);
};

export default Widget;
