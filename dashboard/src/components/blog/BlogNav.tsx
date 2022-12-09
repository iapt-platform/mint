import { Link } from "react-router-dom";
import { useIntl } from "react-intl";
import { MailOutlined } from "@ant-design/icons";
import type { MenuProps } from "antd";
import { Menu, Row, Col } from "antd";

interface IWidgetBlogNav {
	selectedKey: string;
	studio: string;
}
const Widget = (prop: IWidgetBlogNav) => {
	//Library head bar
	const intl = useIntl(); //i18n
	// TODO

	const items: MenuProps["items"] = [
		{
			label: <Link to={`/blog/${prop.studio}/overview`}>{intl.formatMessage({ id: "blog.overview" })}</Link>,
			key: "overview",
			icon: <MailOutlined />,
		},
		{
			label: <Link to={`/blog/${prop.studio}/palicanon`}>{intl.formatMessage({ id: "blog.palicanon" })}</Link>,
			key: "palicanon",
			icon: <MailOutlined />,
		},
		{
			label: (
				<Link to={`/blog/${prop.studio}/course`}>
					{intl.formatMessage({ id: "columns.library.course.title" })}
				</Link>
			),
			key: "course",
			icon: <MailOutlined />,
		},
		{
			label: (
				<Link to={`/blog/${prop.studio}/anthology`}>
					{intl.formatMessage({ id: "columns.library.anthology.title" })}
				</Link>
			),
			key: "anthology",
			icon: <MailOutlined />,
		},
		{
			label: (
				<Link to={`/blog/${prop.studio}/term`}>{intl.formatMessage({ id: "columns.library.term.title" })}</Link>
			),
			key: "term",
			icon: <MailOutlined />,
		},
	];
	return (
		<Row>
			<Col flex="300px"></Col>

			<Col flex="auto">
				<Menu selectedKeys={[prop.selectedKey]} mode="horizontal" items={items} />
			</Col>
		</Row>
	);
};
export default Widget;
