import { Button } from "antd";
import { useState } from "react";
import { Link } from "react-router-dom";

const Widget = () => {
	const [userName, setUserName] = useState("Kosalla_China");

	return (
		<>
			<Link to={`/studio/${userName}/home`}>
				<Button type="primary">藏经阁</Button>
			</Link>
		</>
	);
};

export default Widget;
