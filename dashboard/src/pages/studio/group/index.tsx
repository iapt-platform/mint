import { Layout } from "antd";
import { Outlet } from "react-router-dom";
import LeftSider from "../../../components/studio/LeftSider";

const { Content } = Layout;

const Widget = () => {
	return (
		<Layout>
			<Layout>
				<LeftSider selectedKeys="group" />
				<Content>
					<Outlet />
				</Content>
			</Layout>
		</Layout>
	);
};

export default Widget;
