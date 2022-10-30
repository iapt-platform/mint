import { Outlet } from "react-router-dom";
import { Layout } from "antd";

import LeftSider from "../../../components/studio/LeftSider";

const { Content } = Layout;

const Widget = () => {
	return (
		<Layout>
			<Layout>
				<LeftSider selectedKeys="userdict" />
				<Content>
					<Outlet />
				</Content>
			</Layout>
		</Layout>
	);
};

export default Widget;
