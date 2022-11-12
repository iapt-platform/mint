import { Button, Card, Dropdown, Menu, Space, Segmented } from "antd";
import { MoreOutlined, MenuOutlined } from "@ant-design/icons";
import type { MenuProps } from "antd";
import ArticleView, { IWidgetArticleData } from "./ArticleView";

interface IWidgetArticleCard {
	data?: IWidgetArticleData;
}
const Widget = ({ data }: IWidgetArticleCard) => {
	const onClick: MenuProps["onClick"] = (e) => {
		console.log("click ", e);
	};

	const menu = (
		<Menu
			onClick={onClick}
			items={[
				{
					key: "close",
					label: "关闭",
				},
				{
					key: "closeAll",
					label: "关闭全部",
				},
				{
					key: "closeOthers",
					label: "关闭其他",
				},
				{
					key: "closeRight",
					label: "关闭右侧",
				},
			]}
		/>
	);

	return (
		<Card
			size="small"
			title={
				<Space>
					<Button size="small" icon={<MenuOutlined />} />
					{data?.title}
				</Space>
			}
			extra={
				<Space>
					<Segmented
						options={[
							{ label: "阅读", value: "read" },
							{ label: "编辑", value: "edit" },
						]}
						value="read"
						onChange={(value) => {
							console.log(value);
						}}
					/>
					<Dropdown overlay={menu} placement="bottomRight">
						<Button shape="circle" icon={<MoreOutlined />}></Button>
					</Dropdown>
				</Space>
			}
			style={{ width: 600 }}
			bodyStyle={{ height: 590, overflowY: "scroll" }}
		>
			<ArticleView {...data} />
		</Card>
	);
};

export default Widget;
