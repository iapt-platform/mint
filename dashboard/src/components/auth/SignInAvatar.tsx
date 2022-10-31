import { Dropdown, Tooltip } from "antd";
import { Avatar } from "antd";
import { ProCard } from "@ant-design/pro-components";
import { UserOutlined, HomeOutlined, LogoutOutlined, SettingOutlined } from "@ant-design/icons";

const userCard = (
	<>
		<ProCard
			style={{ maxWidth: 500, minWidth: 300 }}
			actions={[
				<SettingOutlined key="setting" />,
				<HomeOutlined key="edit" />,
				<Tooltip title="退出登录">
					<LogoutOutlined key="ellipsis" />
				</Tooltip>,
			]}
		>
			<div>
				<h2>kosalla</h2>
				<div>遨游法的海洋</div>
			</div>
		</ProCard>
	</>
);
const Widget = () => {
	// TODO
	return (
		<Dropdown overlay={userCard} placement="bottomRight" arrow={{ pointAtCenter: true }}>
			<Avatar style={{ backgroundColor: "#87d068" }} icon={<UserOutlined />} />
		</Dropdown>
	);
};

export default Widget;
