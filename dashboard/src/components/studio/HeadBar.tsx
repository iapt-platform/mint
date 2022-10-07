import { Link } from "react-router-dom";
import { Space } from "antd";

const Widget = () => {
  return (
  	<div>
		studio head bar
		<Space>
			<Link to="/">首页</Link>
		</Space>
	</div>
  );
};

export default Widget;
