import { Button } from "antd";
import { Link } from "react-router-dom";

const Widget = () => {
	return (
		<>
			<Link to="/palicanon/list">
				<Button type="primary">藏经阁</Button>
			</Link>
		</>
	);
};

export default Widget;
