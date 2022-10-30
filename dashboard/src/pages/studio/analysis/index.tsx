import { Outlet } from "react-router-dom";
import { Layout } from "antd";

import LeftSider from "../../../components/studio/LeftSider";

const { Content } = Layout;

const Widget = () => {
	return (
		<Layout>
			<LeftSider selectedKeys="analysis" />
			<Content>
				<Outlet />
			</Content>
		</Layout>
	);
};

export default Widget;
