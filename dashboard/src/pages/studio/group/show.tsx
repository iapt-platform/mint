import { useParams } from "react-router-dom";
import { useIntl } from "react-intl";
import { Layout } from "antd";
import { Col, Row } from "antd";
import GroupFile from "../../../components/studio/group/GroupFile";
import GroupMember from "../../../components/studio/group/GroupMember";

const { Content } = Layout;

const Widget = () => {
	const intl = useIntl();
	const { studioname, groupid } = useParams(); //url 参数
	return (
		<Content>
			<Row>
				<Col flex="auto">
					<GroupFile groupid={groupid} />
				</Col>
				<Col flex="400px">
					<GroupMember groupid={groupid} />
				</Col>
			</Row>
		</Content>
	);
};

export default Widget;
