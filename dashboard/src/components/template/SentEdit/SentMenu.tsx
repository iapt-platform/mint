import { useState } from "react";
import { Button, Dropdown, Menu } from "antd";
import { MoreOutlined } from "@ant-design/icons";

interface ISentMenu {
	children?: React.ReactNode;
}
const Widget = ({ children }: ISentMenu) => {
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

export default Widget;
