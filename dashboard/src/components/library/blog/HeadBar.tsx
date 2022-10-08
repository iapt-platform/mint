import { Link } from "react-router-dom";
import { Space } from "antd";
import { useIntl } from "react-intl";

const Widget = () => {
	//Library head bar
	const intl = useIntl();//i18n
	// TODO
  return (
	<Space>
		<Link to="/community">
			{intl.formatMessage({ id: "columns.library.community.title" })}
		</Link>
		<Link to="/palicanon">
			{intl.formatMessage({ id: "columns.library.palicanon.title" })}
		</Link>
		<Link to="/course">
			{intl.formatMessage({ id: "columns.library.course.title" })}
		</Link>
		<Link to="/term">
			{intl.formatMessage({ id: "columns.library.term.title" })}
		</Link>
		<Link to="/dict">
			{intl.formatMessage({ id: "columns.library.dict.title" })}
		</Link>
		<Link to="/anthology">
			{intl.formatMessage({ id: "columns.library.anthology.title" })}
		</Link>
	</Space>
  );
};

export default Widget;
