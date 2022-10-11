import { useParams, Link } from "react-router-dom";
import { useIntl } from "react-intl";
import { Layout, Breadcrumb } from "antd";
import { Col, Row } from "antd";
import HeadBar from "../../../components/studio/HeadBar";
import LeftSider from "../../../components/studio/LeftSider";
import Footer from "../../../components/studio/Footer";
import GroupFile from "../../../components/studio/group/GroupFile";
import GroupMember from "../../../components/studio/group/GroupMember";

const { Content } = Layout;

const Widget = () => {
	const intl = useIntl();
	const { studioname, groupid } = useParams(); //url 参数
	const linkStudio = `/studio/${studioname}`;
	const linkGroup = `${linkStudio}/group`;
	return (
		<Layout>
			<HeadBar />
			<Layout>
				<LeftSider selectedKeys="group" />
				<Content>
					<Breadcrumb>
						<Breadcrumb.Item>
							<Link to={linkStudio}>{intl.formatMessage({ id: "columns.studio.title" })}</Link>
						</Breadcrumb.Item>
						<Breadcrumb.Item>
							{intl.formatMessage({ id: "columns.studio.collaboration.title" })}
						</Breadcrumb.Item>
						<Breadcrumb.Item>
							<Link to={linkGroup}>{intl.formatMessage({ id: "columns.studio.group.title" })}</Link>
						</Breadcrumb.Item>
						<Breadcrumb.Item>{groupid}</Breadcrumb.Item>
					</Breadcrumb>
					<Row>
						<Col flex="auto">
							<GroupFile groupid={groupid} />
						</Col>
						<Col flex="400px">
							<GroupMember groupid={groupid} />
						</Col>
					</Row>
				</Content>
			</Layout>
			<Footer />
		</Layout>
	);
};

export default Widget;
