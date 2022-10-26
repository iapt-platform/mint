import { Link } from "react-router-dom";
import { Layout, Row, Col } from "antd";
import { useIntl } from "react-intl";

const { Footer } = Layout;

const Widget = () => {
	//Library foot bar
	const intl = useIntl(); //i18n
	// TODO
	return (
		<Footer>
			<Row>
				<Col span={8}>相关链接</Col>
				<Col span={16}>
					问题收集<Link to="/">{intl.formatMessage({ id: "columns.library.palicanon.title" })}</Link>
				</Col>
			</Row>
			<Row>
				<Col>Powered by PCDS</Col>
			</Row>
		</Footer>
	);
};

export default Widget;
