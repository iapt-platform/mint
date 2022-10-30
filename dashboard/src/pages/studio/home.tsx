import { useParams, Link } from "react-router-dom";
import { useIntl } from "react-intl";
import { Layout } from "antd";

import LeftSider from "../../components/studio/LeftSider";

const { Content } = Layout;

const Widget = () => {
	const intl = useIntl(); //i18n
	const { studioname } = useParams(); //url 参数
	return (
		<Layout>
			<LeftSider />
			<Content>
				<h2>
					{intl.formatMessage({ id: "columns.studio.title" })}/{studioname}/首页
				</h2>
				<div>
					<Link to=""> </Link>
				</div>
			</Content>
		</Layout>
	);
};

export default Widget;
