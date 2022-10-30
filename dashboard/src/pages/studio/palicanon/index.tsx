import { useParams, Link } from "react-router-dom";
import { useIntl } from "react-intl";
import { Space } from "antd";

import LeftSider from "../../../components/studio/LeftSider";

const Widget = () => {
	const intl = useIntl(); //i18n
	const { studioname } = useParams(); //url 参数
	return (
		<div>
			<LeftSider />
			<h2>
				studio/{studioname}/{intl.formatMessage({ id: "columns.studio.palicanon.title" })}
			</h2>
			<div>
				<Space>
					<Link to=""> </Link>
				</Space>
			</div>
		</div>
	);
};

export default Widget;
