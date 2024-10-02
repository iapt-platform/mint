import { Link } from "react-router-dom";
import { useIntl } from "react-intl";

const Widget = () => {
	//Library foot bar
	const intl = useIntl(); //i18n
	// TODO
	return (
		<div>
			<div>
				<div>相关链接</div>
				<div>
					问题收集<Link to="/">{intl.formatMessage({ id: "columns.library.palicanon.title" })}</Link>
				</div>
			</div>
			<div>
				<div>Powered by PCDS</div>
			</div>
		</div>
	);
};

export default Widget;
