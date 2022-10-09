import { Link } from "react-router-dom";
import { Space } from "antd";
import { useIntl } from "react-intl";

const Widget = () => {
	//Library foot bar
	const intl = useIntl();//i18n
	// TODO
  return (
	<div>
		<p>底部区域</p>
		<Space>

			<Link to="/">
				联系方式
			</Link>
			<Link to="/">
				{intl.formatMessage({ id: "columns.library.palicanon.title" })}
			</Link>
		</Space>		
	</div>

  );
};

export default Widget;
