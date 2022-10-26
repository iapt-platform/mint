import { Dropdown, Menu, Button } from "antd";
import { GlobalOutlined } from "@ant-design/icons";
import type { MenuProps } from "antd";

const onClick: MenuProps["onClick"] = (e) => {
	console.log("click ", e);
};

const menu = (
	<Menu
		onClick={onClick}
		items={[
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
		]}
	/>
);
const Widget = () => {
	// TODO
	return (
		<Dropdown overlay={menu} placement="bottomRight">
			<Button ghost icon={<GlobalOutlined />}>
				简体中文
			</Button>
		</Dropdown>
	);
};

export default Widget;
